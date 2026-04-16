import type { User } from '~/types'
import { handleSubscription403 } from '~/composables/useSubscription'
import { callWithNuxt } from '#app'

/**
 * Cookie name for the API bearer token.
 * Each subdomain gets its own cookie (host-scoped).
 * Cross-subdomain auth is handled by passing the token in the redirect URL.
 */
const TOKEN_COOKIE = 'auth.token'

/**
 * Simple authentication composable.
 *
 * - Stores the bearer token in a host-scoped cookie
 * - Provides login / logout / fetchUser
 * - All API calls go through a plain $fetch with the Authorization header
 *
 * Cross-subdomain flow:
 *   1. User logs in on localhost:3000 → cookie set for localhost
 *   2. Redirect to test.localhost:3000/dashboard?_token=xxx
 *   3. Middleware reads _token, calls setToken(), strips the param
 *   4. Subsequent requests on test.localhost use the local cookie
 */
export function useAuth() {
    const user = useState<User | null>('auth.user', () => null)
    const config = useRuntimeConfig()
    const baseUrl = config.public.apiBaseUrl as string

    // ── Single cookie ref ──
    // Host-scoped (no domain attribute) so it works reliably on every host.
    const tokenCookie = useCookie(TOKEN_COOKIE, {
        path: '/',
        sameSite: 'lax' as const,
        maxAge: 60 * 60 * 24 * 365, // 1 year
    })

    function getToken(): string | null {
        return tokenCookie.value ?? null
    }

    function setToken(token: string | null) {
        tokenCookie.value = token
    }

    // ── Authenticated fetch helper ──

    function authFetch<T = unknown>(url: string, opts: Record<string, any> = {}): Promise<T> {
        const token = getToken()
        const headers: Record<string, string> = {
            Accept: 'application/json',
            ...(opts.headers ?? {}),
        }

        if (token) {
            headers['Authorization'] = `Bearer ${token}`
        }

        return $fetch<T>(url, {
            baseURL: baseUrl,
            ...opts,
            headers,
        }).catch((err) => {
            // Intercept plan-gating 403s so the UI reacts immediately.
            // handleSubscription403 is a plain function — no Nuxt context needed.
            const result = handleSubscription403(err)
            if (result.handled && result.type === 'trial_expired' && import.meta.client) {
                navigateTo('/settings/billing')
            }
            // limit_reached is handled at the call site (show modal/toast with context).
            throw err
        }) as Promise<T>
    }

    // ── Auth actions ──

    async function fetchUser(): Promise<User | null> {
        const token = getToken()
        if (!token) {
            user.value = null
            return null
        }

        try {
            const data = await authFetch<User>('/api/user')
            user.value = data
            return data
        } catch {
            // Token expired / invalid
            user.value = null
            setToken(null)
            return null
        }
    }

    interface LoginResponse {
        token: string
        user: User
        token_type: string
    }

    async function login(credentials: { email: string; password: string }): Promise<User> {
        const nuxtApp = useNuxtApp()

        // The API returns { user, token, token_type } in one shot
        const response = await $fetch<LoginResponse>('/api/login', {
            baseURL: baseUrl,
            method: 'POST',
            body: credentials,
            headers: { Accept: 'application/json' },
        })

        // Store the token in the cookie (single ref, immediately readable)
        setToken(response.token)

        // Use the user from the response directly — no second request needed
        user.value = response.user

        // callWithNuxt restores context lost after the await above.
        await callWithNuxt(nuxtApp, () => useSubscription().fetchSubscription(authFetch))

        return response.user
    }

    async function logout() {
        try {
            await authFetch('/api/logout', { method: 'POST' })
        } catch {
            // Ignore errors — we're logging out regardless
        } finally {
            user.value = null
            setToken(null)
            // Clear subscription state without calling useSubscription() composable.
            if (import.meta.client) {
                try {
                    useNuxtApp().payload.state['subscription'] = null
                } catch { /* ignore */ }
            }
        }
    }

    /**
     * Initialize auth state on page load.
     * Reads the token from the cookie, fetches /api/user if present.
     * Call this early (middleware) so guards have access to `user`.
     */
    async function init() {
        if (user.value) return // Already hydrated
        const nuxtApp = useNuxtApp()
        const u = await fetchUser()
        if (u) {
            // callWithNuxt restores the Nuxt async context lost after the await above.
            await callWithNuxt(nuxtApp, () => useSubscription().fetchSubscription(authFetch))
        }
    }

    const isAuthenticated = computed(() => !!user.value)

    return {
        user,
        isAuthenticated,
        getToken,
        setToken,
        authFetch,
        fetchUser,
        login,
        logout,
        init,
    }
}
