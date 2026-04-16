/**
 * Routes that are allowed on the root domain (no tenant required).
 * Everything else (dashboard, invoices, products, etc.) requires a tenant.
 */
const PUBLIC_ROUTES = [
    '/',
    '/auth/login',
    '/auth/register',
    '/auth/forgot-password',
    '/auth/reset-password',
    '/onboarding',
    '/tenant-error',
]

/**
 * Check if a route path matches a public route.
 * Matches exact paths and also /store/* and /offers/*
 */
function isPublicRoute(path: string): boolean {
    if (PUBLIC_ROUTES.includes(path)) return true
    if (path.startsWith('/store')) return true
    if (path.startsWith('/admin')) return true
    if (/^\/offers\/[^/]+\/respond$/.test(path)) return true
    return false
}

/**
 * Global route middleware that:
 *
 * 1. Ensures the auth identity is loaded before any route guard runs.
 * 2. Resolves the tenant subdomain when present.
 * 3. Redirects to /onboarding on root-domain app pages.
 */
export default defineNuxtRouteMiddleware(async (to) => {
    // Skip the tenant error page itself to avoid redirect loops
    if (to.path === '/tenant-error') return

    // ── 0. Pick up token from cross-subdomain redirect ──
    // When a user logs in on the root domain and gets redirected
    // to a tenant subdomain, the token is passed as ?_token=xxx.
    // Store it in the local cookie and strip the param from the URL.
    if (import.meta.client) {
        const tokenParam = to.query._token as string | undefined
        if (tokenParam) {
            const { setToken } = useAuth()
            setToken(tokenParam)

            // Strip _token from the URL to keep it clean
            const query = { ...to.query }
            delete query._token
            return navigateTo({ path: to.path, query }, { replace: true })
        }
    }

    // ── 1. Hydrate auth state ──
    // Read the token from the cookie and fetch /api/user so that
    // guards below have access to the current user.
    const { init } = useAuth()
    await init()

    // ── 2. Tenant subdomain resolution ──
    const { hasTenant, isReady, tenantError, resolve, extractSubdomain, tenantSlug } = useTenant()

    // Ensure tenantSlug is always set from the subdomain, even before resolve()
    if (!tenantSlug.value) {
        tenantSlug.value = extractSubdomain()
    }

    // Always resolve the tenant when on a subdomain, regardless of the route.
    // Previously this was skipped for "public" routes, which caused / on a
    // subdomain to hit the tenant-error page because isReady was never set.
    if (hasTenant.value && !isReady.value && !tenantError.value) {
        await resolve()
    }

    // If we're on a subdomain but tenant failed to resolve, show error
    if (hasTenant.value && !isReady.value) {
        return navigateTo('/tenant-error')
    }

    // ── 2b. Tenant ownership guard ──
    // The resolved tenant must match the logged-in user's tenant.
    // This prevents a user from accessing another tenant's subdomain
    // with their own token.
    if (hasTenant.value && isReady.value && !isPublicRoute(to.path)) {
        const { user } = useAuth()
        const { tenant } = useTenant()

        if (user.value && tenant.value && user.value.tenant_id !== tenant.value.id) {
            // User is authenticated but belongs to a different tenant — boot them out
            const { logout } = useAuth()
            await logout()
            return navigateTo('/tenant-error?reason=access_denied')
        }
    }

    // ── 3. Tenant root redirect ──
    // On a subdomain, hitting "/" should never show the marketing landing page.
    // Send authenticated users to their dashboard, everyone else to login.
    if (hasTenant.value && to.path === '/') {
        const { isAuthenticated } = useAuth()
        return navigateTo(isAuthenticated.value ? '/dashboard' : '/auth/login')
    }

    // ── 4. Tenant auth guard ──
    // Protected pages on a subdomain require the user to be logged in.
    if (hasTenant.value && !isPublicRoute(to.path)) {
        const { isAuthenticated } = useAuth()
        if (!isAuthenticated.value) {
            return navigateTo('/auth/login')
        }
    }

    // ── 5. Root-domain guard ──
    // If we're on the root domain and navigating to an app page
    // that requires a tenant, redirect to onboarding
    if (!hasTenant.value && !isPublicRoute(to.path)) {
        return navigateTo('/onboarding')
    }
})
