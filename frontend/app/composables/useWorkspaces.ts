import type { User } from '~/types'

/**
 * Composable for tenant-aware post-login routing.
 *
 * Since each user belongs to exactly one tenant (1:1 via user.tenant_id),
 * there's no workspace picker. This composable provides helpers to build
 * tenant subdomain URLs and redirect after login based on the user's state.
 */
export function useWorkspaces() {
    const config = useRuntimeConfig()
    const appDomain = config.public.appDomain as string

    /**
     * Build the full URL to a tenant's subdomain.
     *
     * Examples:
     *   buildTenantUrl('acme')
     *   → "https://acme.easyrp.com/dashboard" (production)
     *   → "http://acme.localhost:3000/dashboard" (dev)
     */
    function buildTenantUrl(subdomain: string, path: string = '/dashboard'): string {
        const { protocol, port } = useRequestURL()
        const portSuffix = port ? `:${port}` : ''
        return `${protocol}//${subdomain}.${appDomain}${portSuffix}${path}`
    }

    /**
     * Navigate to a tenant's subdomain via full page redirect.
     */
    function goToWorkspace(subdomain: string, path: string = '/dashboard') {
        const url = buildTenantUrl(subdomain, path)

        if (import.meta.client) {
            window.location.href = url
        }
    }

    /**
     * After login, route the user to the right place:
     * - Has a tenant with a known subdomain → redirect to tenant subdomain dashboard
     * - No tenant yet → redirect to /onboarding to create one
     *
     * The auth token is passed via query parameter so the subdomain
     * can pick it up (cookies with domain=.localhost are unreliable).
     */
    function redirectAfterLogin(user: User) {
        if (user.tenant_id && user.tenant?.subdomain) {
            const { getToken } = useAuth()
            const token = getToken()
            const path = token
                ? `/auth/callback?token=${encodeURIComponent(token)}`
                : '/dashboard'
            goToWorkspace(user.tenant.subdomain, path)
        } else {
            navigateTo('/onboarding')
        }
    }

    return {
        buildTenantUrl,
        goToWorkspace,
        redirectAfterLogin,
    }
}
