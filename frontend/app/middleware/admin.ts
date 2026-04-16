/**
 * Named route middleware that protects admin pages.
 * Redirects non-admin users to /.
 */
export default defineNuxtRouteMiddleware(() => {
    const { user, isAuthenticated } = useAuth()

    if (!isAuthenticated.value) {
        return navigateTo('/auth/login')
    }

    if (!user.value?.is_site_admin) {
        return navigateTo('/')
    }
})
