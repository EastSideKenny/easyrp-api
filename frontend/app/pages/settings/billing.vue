<template>
    <div class="max-w-3xl mx-auto space-y-8">
        <UiAppSectionHeader
            title="Plan & Billing"
            description="View your current plan, trial status, and feature usage. Change plans when you need more capacity."
        />

        <UiAppCard v-if="subscriptionLoading" :no-padding="true">
            <div class="flex items-center justify-center py-16 text-text-muted">
                <svg
                    class="w-5 h-5 animate-spin mr-2"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    />
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                    />
                </svg>
                Loading…
            </div>
        </UiAppCard>

        <template v-else-if="subscription">
            <UiAppCard title="Current Plan">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-semibold text-text">
                            {{ currentPlanMerged?.name ?? "Unknown Plan" }}
                        </p>
                        <p class="text-sm text-text-muted mt-1">
                            Status:
                            <UiAppBadge :variant="statusBadge" :dot="false">{{
                                statusLabel
                            }}</UiAppBadge>
                        </p>
                    </div>
                    <div v-if="currentPlanMerged" class="text-right">
                        <p class="text-2xl font-bold text-text tabular-nums">
                            {{
                                formatPlanAmount(
                                    currentPlanMerged,
                                    billingCycle,
                                )
                            }}
                            <span class="text-sm font-normal text-text-muted">{{
                                billingCycle === "yearly" ? "/yr" : "/mo"
                            }}</span>
                        </p>
                        <p
                            v-if="billingVsWorkspaceCurrencyHint"
                            class="text-xs text-text-muted mt-1 max-w-[14rem] ml-auto text-balance"
                        >
                            {{ billingVsWorkspaceCurrencyHint }}
                        </p>
                    </div>
                </div>
            </UiAppCard>

            <UiAppCard
                v-if="isTrialing && !subscription.stripe_subscription_id"
                title="Trial Period"
            >
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-text-secondary"
                            >Days remaining</span
                        >
                        <span
                            class="text-sm font-semibold"
                            :class="isUrgent ? 'text-danger' : 'text-text'"
                        >
                            {{ daysLeft }} {{ daysLeft === 1 ? "day" : "days" }}
                        </span>
                    </div>
                    <div class="w-full bg-surface-alt rounded-full h-2">
                        <div
                            class="h-2 rounded-full transition-all duration-300"
                            :class="isUrgent ? 'bg-danger' : 'bg-primary'"
                            :style="{
                                width: `${Math.max(5, 100 - (daysLeft / 14) * 100)}%`,
                            }"
                        />
                    </div>
                    <p
                        v-if="subscription.trial_ends_at"
                        class="text-xs text-text-muted"
                    >
                        Trial ends on
                        {{
                            new Date(
                                subscription.trial_ends_at,
                            ).toLocaleDateString()
                        }}
                    </p>
                </div>
            </UiAppCard>

            <UiAppCard v-if="isExpired">
                <div class="flex items-center gap-3 text-danger">
                    <AlertCircle class="w-5 h-5 shrink-0" />
                    <div>
                        <p class="font-semibold">Your trial has ended</p>
                        <p class="text-sm text-text-muted mt-0.5">
                            Choose a plan below and add a card if required to
                            restore access.
                        </p>
                    </div>
                </div>
            </UiAppCard>

            <UiAppCard title="Change plan">
                <SubscriptionsPlanSelector
                    v-model="selectedPlanId"
                    :billing-cycle="billingCycle"
                    :plans="billingSelectablePlans"
                    :plans-loading="plansLoading"
                    :current-plan-id="subscription.plan_id"
                    @update:billing-cycle="billingCycle = $event"
                />

                <div v-if="showPaymentFields" class="mt-6 space-y-4">
                    <UiAppFormField
                        label="Name on card"
                        id="billing-card-name"
                    >
                        <input
                            id="billing-card-name"
                            v-model="cardholderName"
                            type="text"
                            autocomplete="cc-name"
                            class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                            placeholder="Full name"
                        />
                    </UiAppFormField>
                    <ClientOnly>
                        <SubscriptionsStripeCardForm
                            ref="stripeCardRef"
                            :disabled="planActionLoading"
                        />
                    </ClientOnly>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <UiAppButton
                        :loading="planActionLoading"
                        :disabled="!canSubmitPlanChange || planActionLoading"
                        @click="applyPlanChange"
                    >
                        {{
                            selectedPlanId === subscription.plan_id
                                ? "Current plan"
                                : "Update plan"
                        }}
                    </UiAppButton>
                </div>
            </UiAppCard>

            <UiAppCard v-if="usageList.length" title="Feature Usage">
                <div class="space-y-4">
                    <UiAppUsageMeter
                        v-for="u in usageList"
                        :key="u.feature"
                        :usage="u"
                    />
                </div>
            </UiAppCard>
        </template>

        <UiAppCard v-else>
            <div class="text-center py-6 text-text-muted space-y-4">
                <p class="text-lg font-medium text-text mb-1">
                    No active subscription
                </p>
                <p class="text-sm max-w-md mx-auto">
                    Pick a paid plan for this workspace — add a card when prompted.
                </p>
            </div>

            <SubscriptionsPlanSelector
                v-model="selectedPlanId"
                :billing-cycle="billingCycle"
                :plans="billingSelectablePlans"
                :plans-loading="plansLoading"
                :current-plan-id="null"
                @update:billing-cycle="billingCycle = $event"
            />

            <div v-if="selectedPlan && !selectedPlan.is_free" class="mt-6 space-y-4">
                <UiAppFormField label="Name on card" id="billing-card-name-empty">
                    <input
                        id="billing-card-name-empty"
                        v-model="cardholderName"
                        type="text"
                        autocomplete="cc-name"
                        class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                        placeholder="Full name"
                    />
                </UiAppFormField>
                <ClientOnly>
                    <SubscriptionsStripeCardForm
                        ref="stripeCardRefEmpty"
                        :disabled="planActionLoading"
                    />
                </ClientOnly>
            </div>

            <div class="mt-6 flex justify-center">
                <UiAppButton
                    :loading="planActionLoading"
                    :disabled="!selectedPlanId || planActionLoading"
                    @click="applyNewSubscription"
                >
                    Activate plan
                </UiAppButton>
            </div>
        </UiAppCard>
    </div>
</template>

<script setup lang="ts">
import { AlertCircle } from "lucide-vue-next";
import type { FeatureUsage, Plan } from "~/types";

definePageMeta({
    layout: "default",
    middleware: ["auth", "tenant-admin"],
});

const { authFetch, user } = useAuth();
const toast = useToast();
const { stripeError } = useStripe();
const { formatCurrency, tenantCurrency } = useCurrency();

const {
    subscription,
    subscriptionLoading,
    fetchSubscription,
    isTrialing,
    isExpired,
    isUrgent,
    daysLeft,
    plans,
    plansLoading,
    fetchPlans,
    subscribeToFreePlan,
    subscribeToPaidPlan,
    changePlan,
} = useSubscription();

const billingCycle = ref<"monthly" | "yearly">("monthly");
const selectedPlanId = ref<number | null>(null);
const cardholderName = ref("");
const planActionLoading = ref(false);
const stripeCardRef = ref<{
    getPaymentMethodId: (b?: { name?: string; email?: string }) => Promise<string | null>;
} | null>(null);
const stripeCardRefEmpty = ref<{
    getPaymentMethodId: (b?: { name?: string; email?: string }) => Promise<string | null>;
} | null>(null);

/** Omit signup free-trial plan unless this workspace is currently on it */
const billingSelectablePlans = computed((): Plan[] => {
    const list = plans.value;
    const ftp = list.find((p) => p.slug === "free_trial");
    if (!ftp) return list;
    const sub = subscription.value;
    const onFreeTrialNow = !!sub?.plan_id && sub.plan_id === ftp.id;
    if (onFreeTrialNow) return list;
    return list.filter((p) => p.id !== ftp.id);
});

/** Merge catalog row (currency / is_free) with subscription payload */
const currentPlanMerged = computed((): Plan | undefined => {
    const sub = subscription.value;
    if (!sub?.plan_id) return undefined;
    const catalog = plans.value.find((p) => p.id === sub.plan_id);
    const apiPlan = sub.plan as Plan | undefined;
    if (!catalog && !apiPlan) return undefined;
    if (!apiPlan) return catalog;
    if (!catalog) return apiPlan;
    return {
        ...catalog,
        ...apiPlan,
        currency: catalog.currency ?? apiPlan.currency,
        price_monthly: catalog.price_monthly ?? apiPlan.price_monthly,
        price_yearly: catalog.price_yearly ?? apiPlan.price_yearly,
        is_free: catalog.is_free ?? apiPlan.is_free,
        features: catalog.features ?? apiPlan.features,
    };
});

const selectedPlan = computed(
    () =>
        billingSelectablePlans.value.find((p) => p.id === selectedPlanId.value) ??
        null,
);

const showPaymentFields = computed(() => {
    if (!subscription.value || !selectedPlan.value) return false;
    if (selectedPlan.value.id === subscription.value.plan_id) return false;
    if (selectedPlan.value.is_free) return false;
    return !subscription.value.stripe_subscription_id;
});

const canSubmitPlanChange = computed(() => {
    if (!subscription.value || !selectedPlanId.value) return false;
    if (selectedPlanId.value === subscription.value.plan_id) return false;
    return true;
});

const statusLabel = computed(() => {
    const s = subscription.value?.status;
    if (s === "trialing") return "Trial";
    if (s === "active") return "Active";
    if (s === "expired") return "Expired";
    if (s === "canceled") return "Canceled";
    if (s === "past_due") return "Past Due";
    return s ?? "Unknown";
});

const statusBadge = computed(() => {
    const s = subscription.value?.status;
    if (s === "active") return "success" as const;
    if (s === "trialing") return "warning" as const;
    if (s === "expired" || s === "canceled") return "danger" as const;
    return "neutral" as const;
});

function planBillingCurrency(plan: Plan | undefined): string {
    return (
        plan?.currency ??
        plans.value.find((p) => p.currency)?.currency ??
        "USD"
    ).toUpperCase();
}

function formatPlanAmount(
    plan: Plan,
    cycle: "monthly" | "yearly",
): string {
    const cur = planBillingCurrency(plan);
    if (plan.is_free) {
        return formatCurrency(0, cur);
    }
    const amount =
        cycle === "yearly" ? plan.price_yearly : plan.price_monthly;
    return formatCurrency(amount, cur);
}

/** When workspace invoice currency differs from Stripe billing currency */
const billingVsWorkspaceCurrencyHint = computed(() => {
    const bill = planBillingCurrency(currentPlanMerged.value);
    const ws = tenantCurrency.value.toUpperCase();
    if (!currentPlanMerged.value || bill === ws) return "";
    return `Subscription prices are in ${bill}. Your workspace uses ${ws} on invoices and quotes.`;
});

const usageList = computed<FeatureUsage[]>(() => {
    const raw = subscription.value?.usage;
    if (!raw) return [];
    if (Array.isArray(raw)) return raw;
    return Object.entries(raw).map(([feature, data]) => ({
        feature,
        ...(data as Omit<FeatureUsage, "feature">),
    }));
});

onMounted(async () => {
    await fetchPlans(authFetch);
    await fetchSubscription(authFetch);
    if (subscription.value?.plan_id) {
        selectedPlanId.value = subscription.value.plan_id;
    } else if (billingSelectablePlans.value.length) {
        selectedPlanId.value = billingSelectablePlans.value[0]?.id ?? null;
    }
});

watch(
    () => subscription.value?.plan_id,
    (id) => {
        if (id && !planActionLoading.value) selectedPlanId.value = id;
    },
);

watch(
    billingSelectablePlans,
    (list) => {
        const ids = list.map((p) => p.id);
        if (
            planActionLoading.value ||
            selectedPlanId.value === null ||
            ids.includes(selectedPlanId.value)
        ) {
            return;
        }
        const fallback =
            subscription.value?.plan_id &&
            ids.includes(subscription.value.plan_id)
                ? subscription.value.plan_id
                : (ids[0] ?? null);
        selectedPlanId.value = fallback;
    },
    { flush: "post" },
);

async function applyPlanChange() {
    if (!subscription.value || !selectedPlan.value) return;
    planActionLoading.value = true;
    try {
        const target = selectedPlan.value;
        const sub = subscription.value;

        if (target.id === sub.plan_id) {
            toast.info("This plan is already active.");
            return;
        }

        if (!target.is_free && !sub.stripe_subscription_id) {
            const pm = await stripeCardRef.value?.getPaymentMethodId({
                name: cardholderName.value.trim() || undefined,
                email: user.value?.email,
            });
            if (!pm) {
                toast.error(
                    stripeError.value || "Enter a valid card to continue.",
                );
                return;
            }
            await changePlan(
                target.id,
                authFetch,
                billingCycle.value,
                pm,
            );
        } else {
            await changePlan(target.id, authFetch, billingCycle.value);
        }

        await fetchSubscription(authFetch);
        toast.success("Your plan has been updated.");
    } catch (e) {
        toast.apiError(e, "Could not update the plan.");
    } finally {
        planActionLoading.value = false;
    }
}

async function applyNewSubscription() {
    if (!selectedPlan.value) return;
    planActionLoading.value = true;
    try {
        const target = selectedPlan.value as Plan;
        if (target.is_free) {
            await subscribeToFreePlan(target.id, authFetch);
        } else {
            const pm = await stripeCardRefEmpty.value?.getPaymentMethodId({
                name: cardholderName.value.trim() || undefined,
                email: user.value?.email,
            });
            if (!pm) {
                toast.error(
                    stripeError.value || "Enter a valid card to continue.",
                );
                return;
            }
            await subscribeToPaidPlan(
                target.id,
                pm,
                authFetch,
                billingCycle.value,
            );
        }
        await fetchSubscription(authFetch);
        toast.success("Subscription activated.");
    } catch (e) {
        toast.apiError(e, "Could not activate the plan.");
    } finally {
        planActionLoading.value = false;
    }
}
</script>
