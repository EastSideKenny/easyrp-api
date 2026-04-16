/**
 * Named route middleware that restricts pages to tenant owner/admin.
 * Staff users are redirected to /dashboard.
 */
export default defineNuxtRouteMiddleware(() => {
    const { user, isAuthenticated } = useAuth()

    if (!isAuthenticated.value) {
        return navigateTo('/auth/login')
    }

    if (!user.value || !['owner', 'admin'].includes(user.value.role)) {
        return navigateTo('/dashboard')
    }
})
