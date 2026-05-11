/**
 * Named route middleware that enforces module access.
 *
 * Pages declare which module they belong to via route meta:
 *   definePageMeta({ middleware: ['auth', 'module-guard'], requiredModule: 'invoices' })
 *
 * If the tenant's modules list is set and does not include the required module,
 * the user is redirected to /dashboard.
 *
 * If the tenant has no modules list (empty / null / undefined), all modules
 * are considered active (backwards-compatible default).
 */
export default defineNuxtRouteMiddleware((to) => {
    const requiredModule = to.meta.requiredModule as string | undefined
    if (!requiredModule) return

    const { tenant } = useTenant()
    const modules = tenant.value?.modules
    const { hasFeature } = useSubscription()

    // Subscription feature entitlement (plan_features) is the source of truth.
    // If present, allow by feature regardless of stale tenant.modules.
    if (hasFeature(requiredModule)) return

    // No module restriction configured on tenant → allow (legacy fallback).
    if (!modules || modules.length === 0) return

    if (!modules.includes(requiredModule)) {
        return navigateTo('/dashboard')
    }
})
