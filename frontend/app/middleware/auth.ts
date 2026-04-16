/**
 * Named route middleware that protects pages requiring authentication.
 * Redirects unauthenticated users to /auth/login.
 *
 * Usage in a page:
 *   definePageMeta({ middleware: ['auth'] })
 */
export default defineNuxtRouteMiddleware(() => {
    // Auth state is already hydrated by the global tenant middleware,
    // so we can just check the user ref directly.
    const { isAuthenticated } = useAuth()

    if (!isAuthenticated.value) {
        return navigateTo('/auth/login')
    }
})
