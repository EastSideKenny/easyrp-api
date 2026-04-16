/**
 * Nuxt plugin that resolves the current tenant from the subdomain
 * early in the app lifecycle — before any page renders or middleware runs.
 *
 * This ensures `useTenant()` is hydrated and available everywhere.
 * When no subdomain is detected (e.g. root domain / marketing site),
 * this plugin does nothing and the app loads normally.
 */
export default defineNuxtPlugin(async () => {
    const { extractSubdomain, resolve } = useTenant()
    const slug = extractSubdomain()

    // No subdomain = marketing/landing site, skip entirely
    if (!slug) return

    // On the server, we don't have the auth cookie available
    // Middleware will handle tenant resolution on client-side navigation.
    if (import.meta.server) return

    try {
        await resolve()
    } catch {
        // Tenant resolution failed — middleware will handle the redirect
    }
})
