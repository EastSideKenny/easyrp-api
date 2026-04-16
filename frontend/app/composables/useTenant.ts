import type { Tenant } from '~/types'

/**
 * Composable for multi-tenant subdomain resolution.
 *
 * Extracts the tenant subdomain from the current hostname,
 * fetches/caches the Tenant object from the API, and exposes
 * it reactively to the rest of the app.
 *
 * Works on both client and server (SSR) via useRequestURL().
 */
export function useTenant() {
    const tenant = useState<Tenant | null>('current-tenant', () => null)
    const tenantSlug = useState<string | null>('tenant-slug', () => null)
    const tenantLoading = useState<boolean>('tenant-loading', () => false)
    const tenantError = useState<string | null>('tenant-error', () => null)

    const config = useRuntimeConfig()
    const appDomain = config.public.appDomain as string // e.g. "easyrp.com" or "localhost"

    /**
     * Extract the tenant subdomain from the current hostname.
     * Examples:
     *   "acme.easyrp.com"       → "acme"
     *   "acme.localhost"        → "acme"
     *   "easyrp.com"            → null  (no subdomain = marketing/landing)
     *   "localhost"             → null
     *   "localhost:3000"        → null
     *   "acme.localhost:3000"   → "acme"
     */
    function extractSubdomain(): string | null {
        // If appDomain isn't configured, we can't detect subdomains
        if (!appDomain) return null

        const { hostname } = useRequestURL()

        // Strip port if present (useRequestURL already handles this, but be safe)
        const host = hostname.toLowerCase()

        // The app domain without port, e.g. "easyrp.com" or "localhost"
        const baseDomain = appDomain.toLowerCase()

        // If hostname equals the base domain exactly, no subdomain
        if (host === baseDomain) return null

        // Check if hostname ends with `.${baseDomain}`
        const suffix = `.${baseDomain}`
        if (host.endsWith(suffix)) {
            const sub = host.slice(0, -suffix.length)
            // Ensure it's a single-level subdomain (no dots)
            if (sub && !sub.includes('.')) {
                return sub
            }
        }

        return null
    }

    /**
     * Resolve the tenant: extract subdomain and fetch tenant data from API.
     * Call this once early (e.g. in a plugin or middleware).
     */
    async function resolve(): Promise<boolean> {
        const slug = extractSubdomain()
        tenantSlug.value = slug

        // No subdomain = main/marketing site, not a tenant context
        if (!slug) {
            tenant.value = null
            tenantError.value = null
            return true
        }

        // Already resolved this tenant
        if (tenant.value && tenant.value.subdomain === slug) {
            return true
        }

        tenantLoading.value = true
        tenantError.value = null

        try {
            const config = useRuntimeConfig()
            const baseUrl = config.public.apiBaseUrl as string

            // Use a plain fetch — tenant resolution is a public endpoint.
            // The subdomain must resolve regardless of auth state so that
            // unauthenticated users can land on the login page.
            const data = await $fetch<Tenant>(`/api/tenants/resolve/${slug}`, {
                baseURL: baseUrl,
                headers: { Accept: 'application/json' },
            })
            tenant.value = data

            if (!data.is_active) {
                tenantError.value = 'This workspace has been deactivated.'
                return false
            }

            return true
        } catch (err: any) {
            tenant.value = null
            tenantError.value =
                err?.statusCode === 404 || err?.response?.status === 404
                    ? 'Workspace not found. Please check the URL.'
                    : 'Unable to load workspace. Please try again.'
            return false
        } finally {
            tenantLoading.value = false
        }
    }

    /** Whether we're currently within a tenant context (subdomain present) */
    const hasTenant = computed(() => !!tenantSlug.value)

    /** Whether the tenant is fully resolved and active */
    const isReady = computed(() => !!tenant.value && !tenantError.value)

    return {
        tenant,
        tenantSlug,
        tenantLoading,
        tenantError,
        hasTenant,
        isReady,
        extractSubdomain,
        resolve,
    }
}
