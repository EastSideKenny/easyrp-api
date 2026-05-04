import type { FeatureUsage, TenantSubscription, Plan } from '~/types'

/**
 * Error codes returned in 403 bodies.
 *   trial_expired        – trial period ended, no paid plan
 *   subscription_required – generic no-subscription check
 *   limit_reached        – tenant is at their plan's record cap for a feature
 */
const TRIAL_EXPIRED_CODES = ['trial_expired', 'subscription_required']
const LIMIT_REACHED_CODE = 'limit_reached'

/**
 * Shape of the 403 limit_reached payload:
 * { error: 'limit_reached', feature: 'invoices', limit: 25, used: 25 }
 */
export interface LimitReachedError {
    feature: string
    limit: number
    used: number
}

/**
 * Handle a trial_expired or limit_reached 403 response.
 * Safe to call from any async context (plain function, client-guarded).
 *
 * Returns a discriminated result:
 *   { handled: false }                       – not a relevant 403
 *   { handled: true, type: 'trial_expired' }
 *   { handled: true, type: 'limit_reached', ...LimitReachedError }
 */
export function handleSubscription403(
    err: any
): { handled: false } | { handled: true; type: 'trial_expired' } | ({ handled: true; type: 'limit_reached' } & LimitReachedError) {
    const status = err?.response?.status ?? err?.statusCode
    if (status !== 403) return { handled: false }

    const data = err?.response?._data ?? err?.data ?? {}
    const code: string = data?.error ?? data?.code ?? ''

    // ── trial / subscription expired ──
    if (TRIAL_EXPIRED_CODES.includes(code)) {
        if (import.meta.client) {
            try {
                const state = useNuxtApp().payload.state
                const sub = (state['subscription'] ?? null) as TenantSubscription | null
                if (sub) {
                    state['subscription'] = {
                        ...sub,
                        status: 'expired',
                        trial_ends_at: new Date(0).toISOString(),
                    }
                }
            } catch {
                // Nuxt context unavailable — banner updates on next navigation
            }
        }
        return { handled: true, type: 'trial_expired' }
    }

    // ── limit reached ──
    if (code === LIMIT_REACHED_CODE) {
        return {
            handled: true,
            type: 'limit_reached',
            feature: data?.feature ?? '',
            limit: data?.limit ?? 0,
            used: data?.used ?? 0,
        }
    }

    return { handled: false }
}

/**
 * Composable for managing the current tenant's subscription + trial state.
 */
export function useSubscription() {
    const subscription = useState<TenantSubscription | null>('subscription', () => null)
    const subscriptionLoading = useState<boolean>('subscription.loading', () => false)
    const plans = useState<Plan[]>('subscription.plans', () => [])
    const plansLoading = ref(false)

    // ── Fetch ──────────────────────────────────────────────────────────────
    // authFetch is passed in to avoid a circular dependency with useAuth.
    // The API now returns a single object (not an array).

    async function fetchSubscription(authFetch: (url: string, opts?: any) => Promise<any>): Promise<void> {
        try {
            subscriptionLoading.value = true
            const data = await authFetch('/api/subscriptions')

            // Backend returns { subscriptions: [...], usage: {...}, trial_ends_at, status }
            if (data?.subscriptions && Array.isArray(data.subscriptions)) {
                const subs = data.subscriptions as TenantSubscription[]
                const active = subs.find(
                    (s) => s.status === 'trialing' || s.status === 'active'
                ) ?? subs[0] ?? null

                if (active) {
                    subscription.value = {
                        ...active,
                        usage: data.usage ?? active.usage,
                        trial_ends_at: data.trial_ends_at ?? active.trial_ends_at,
                    }
                } else {
                    subscription.value = null
                }
            } else if (Array.isArray(data)) {
                subscription.value =
                    (data as TenantSubscription[]).find(
                        (s) => s.status === 'trialing' || s.status === 'active'
                    ) ??
                    data[0] ??
                    null
            } else {
                subscription.value = (data as TenantSubscription) ?? null
            }
        } catch {
            subscription.value = null
        } finally {
            subscriptionLoading.value = false
        }
    }

    function clearSubscription() {
        subscription.value = null
    }

    // ── Plan subscription methods ──────────────────────────────────────────

    /**
     * Subscribe to a free plan (no payment required).
     */
    async function subscribeToFreePlan(
        planId: number,
        authFetch: (url: string, opts?: any) => Promise<any>
    ): Promise<TenantSubscription | null> {
        try {
            const data = await authFetch('/api/subscriptions/subscribe-free', {
                method: 'POST',
                body: {
                    plan_id: planId,
                },
            })
            if (data?.subscription) {
                subscription.value = data.subscription
                return data.subscription
            }
            return null
        } catch (err) {
            throw err
        }
    }

    /**
     * Subscribe to a paid plan (requires payment method).
     */
    async function subscribeToPaidPlan(
        planId: number,
        paymentMethodId: string,
        authFetch: (url: string, opts?: any) => Promise<any>,
        billingCycle: 'monthly' | 'yearly' = 'monthly'
    ): Promise<TenantSubscription | null> {
        try {
            const data = await authFetch('/api/subscriptions/subscribe-paid', {
                method: 'POST',
                body: {
                    plan_id: planId,
                    payment_method_id: paymentMethodId,
                    billing_cycle: billingCycle,
                },
            })
            if (data?.subscription) {
                subscription.value = data.subscription
                return data.subscription
            }
            return null
        } catch (err) {
            throw err
        }
    }

    /**
     * Change the tenant's current plan. The backend operates on the tenant — Cashier picks
     * up the right Stripe subscription internally — so no subscription id is needed.
     *
     * Pass `paymentMethodId` to upgrade from a free plan to a paid one.
     */
    async function changePlan(
        newPlanId: number,
        authFetch: (url: string, opts?: any) => Promise<any>,
        billingCycle: 'monthly' | 'yearly' = 'monthly',
        paymentMethodId?: string
    ): Promise<TenantSubscription | null> {
        try {
            const body: Record<string, unknown> = {
                plan_id: newPlanId,
                billing_cycle: billingCycle,
            }
            if (paymentMethodId) {
                body.payment_method_id = paymentMethodId
            }

            const data = await authFetch('/api/subscriptions/change-plan', {
                method: 'POST',
                body,
            })
            if (data?.subscription) {
                subscription.value = data.subscription
                return data.subscription
            }
            return null
        } catch (err) {
            throw err
        }
    }

    /**
     * Cancel the tenant's current subscription.
     */
    async function cancelSubscription(
        authFetch: (url: string, opts?: any) => Promise<any>,
        immediately: boolean = false
    ): Promise<void> {
        try {
            await authFetch('/api/subscriptions', {
                method: 'DELETE',
                body: {
                    immediately,
                },
            })
            subscription.value = null
        } catch (err) {
            throw err
        }
    }

    // ── Feature / limit helpers ────────────────────────────────────────────

    /**
     * Normalise usage — backend may return an array OR a plain object map.
     * Always returns a FeatureUsage entry or undefined.
     */
    function usageFor(feature: string): FeatureUsage | undefined {
        const raw = subscription.value?.usage
        if (!raw) return undefined

        // Array shape: [{ feature: 'invoices', limit, used, remaining }, ...]
        if (Array.isArray(raw)) {
            return raw.find((u) => u.feature === feature)
        }

        // Object shape: { invoices: { limit, used, remaining }, ... }
        const entry = (raw as Record<string, Omit<FeatureUsage, 'feature'>>)[feature]
        if (!entry) return undefined
        return { feature, ...entry }
    }

    /**
     * Returns true when the tenant has access to the given feature code.
     * Falls back to true (fail-open) when no usage data is present.
     */
    function hasFeature(feature: string): boolean {
        const raw = subscription.value?.usage
        if (!raw) return true
        if (Array.isArray(raw)) return raw.some((u) => u.feature === feature)
        return feature in (raw as Record<string, unknown>)
    }

    /**
     * Returns true when the tenant has reached or exceeded the limit for a feature.
     * null limit = unlimited → never at limit.
     */
    function isAtLimit(feature: string): boolean {
        const u = usageFor(feature)
        if (!u) return false
        if (u.limit === null) return false
        return u.remaining !== null && u.remaining <= 0
    }

    /**
     * Usage percentage 0–100 (capped). Returns 0 for unlimited features.
     */
    function usagePercent(feature: string): number {
        const u = usageFor(feature)
        if (!u || u.limit === null || u.limit === 0) return 0
        return Math.min(100, Math.round((u.used / u.limit) * 100))
    }

    // ── Computed trial state ───────────────────────────────────────────────

    const isTrialing = computed(() =>
        subscription.value?.status === 'trialing' && !!subscription.value?.trial_ends_at
    )

    const daysLeft = computed((): number => {
        if (!subscription.value?.trial_ends_at) return 0
        const ms = new Date(subscription.value.trial_ends_at).getTime() - Date.now()
        return Math.max(0, Math.ceil(ms / (1000 * 60 * 60 * 24)))
    })

    const isExpired = computed(() =>
        subscription.value?.status === 'expired' ||
        (subscription.value?.status === 'trialing' && daysLeft.value === 0)
    )

    const isUrgent = computed(() => isTrialing.value && !isExpired.value && daysLeft.value <= 3)

    function normalizePlanFromApi(raw: Record<string, unknown>): Plan {
        const monthly = raw.monthly as { price?: number } | undefined
        const yearly = raw.yearly as { price?: number } | undefined
        return {
            id: raw.id as number,
            name: String(raw.name ?? ''),
            slug: String(raw.slug ?? ''),
            price_monthly: monthly?.price ?? 0,
            price_yearly: yearly?.price ?? 0,
            currency:
                typeof raw.currency === 'string'
                    ? raw.currency.toUpperCase()
                    : undefined,
            is_active: true,
            is_free: Boolean(raw.is_free),
            features: Array.isArray(raw.features) ? (raw.features as Plan['features']) : undefined,
            created_at: typeof raw.created_at === 'string' ? raw.created_at : '',
            updated_at: typeof raw.updated_at === 'string' ? raw.updated_at : '',
        }
    }

    return {
        subscription,
        subscriptionLoading,
        fetchSubscription,
        clearSubscription,
        plans,
        plansLoading,
        fetchPlans: async (authFetch: (url: string, opts?: any) => Promise<any>): Promise<void> => {
            try {
                plansLoading.value = true
                const data = await authFetch('/api/subscription-plans')
                if (!Array.isArray(data)) {
                    plans.value = []
                    return
                }
                plans.value = data.map((row) => normalizePlanFromApi(row as Record<string, unknown>))
            } catch {
                plans.value = []
            } finally {
                plansLoading.value = false
            }
        },
        subscribeToFreePlan,
        subscribeToPaidPlan,
        changePlan,
        cancelSubscription,
        // feature gating
        usageFor,
        hasFeature,
        isAtLimit,
        usagePercent,
        // trial state
        isTrialing,
        isExpired,
        isUrgent,
        daysLeft,
    }
}
