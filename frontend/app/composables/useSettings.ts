import type { User } from '~/types'

interface BrandingSettings {
    name: string
    subdomain: string
    currency: string | null
    theme: string
    logo_url: string | null
}

interface TenantUser {
    id: number
    name: string
    email: string
    role: 'owner' | 'admin' | 'staff'
    is_active: boolean
    last_login_at: string | null
    created_at: string
}

export function useSettings() {
    const { authFetch } = useAuth()

    // ── Branding ──

    function fetchBranding() {
        return authFetch<BrandingSettings>('/api/settings/branding')
    }

    function updateBranding(data: { name?: string; currency?: string; theme?: string }) {
        return authFetch<BrandingSettings>('/api/settings/branding', {
            method: 'PATCH',
            body: data,
        })
    }

    function uploadLogo(file: File) {
        const formData = new FormData()
        formData.append('logo', file)
        return authFetch<{ message: string; logo_url: string }>('/api/settings/branding/logo', {
            method: 'POST',
            body: formData,
        })
    }

    function deleteLogo() {
        return authFetch<{ message: string }>('/api/settings/branding/logo', {
            method: 'DELETE',
        })
    }

    // ── Users ──

    function fetchUsers() {
        return authFetch<TenantUser[]>('/api/settings/users')
    }

    function createUser(data: { name: string; email: string; password: string; password_confirmation: string; role: string }) {
        return authFetch<{ message: string; user: TenantUser }>('/api/settings/users', {
            method: 'POST',
            body: data,
        })
    }

    function updateUser(userId: number, data: { name?: string; role?: string; is_active?: boolean }) {
        return authFetch<{ message: string; user: TenantUser }>(`/api/settings/users/${userId}`, {
            method: 'PATCH',
            body: data,
        })
    }

    function deleteUser(userId: number) {
        return authFetch<{ message: string }>(`/api/settings/users/${userId}`, {
            method: 'DELETE',
        })
    }

    return {
        fetchBranding,
        updateBranding,
        uploadLogo,
        deleteLogo,
        fetchUsers,
        createUser,
        updateUser,
        deleteUser,
    }
}
