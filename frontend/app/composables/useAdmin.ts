import type { Tenant, Plan } from '~/types'

interface AdminStats {
    total_tenants: number
    active_tenants: number
    total_subscriptions: number
    active_subscriptions: number
    plans: (Pick<Plan, 'id' | 'name' | 'slug' | 'is_active'> & { tenants_count: number })[]
}

interface PaginatedTenants {
    data: (Tenant & {
        users_count: number
        plan: Pick<Plan, 'id' | 'name' | 'slug'> | null
        subscriptions: {
            id: number
            tenant_id: number
            plan_id: number
            status: string
            trial_ends_at: string | null
            current_period_end: string | null
        }[]
    })[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

export function useAdmin() {
    const { authFetch } = useAuth()

    function fetchStats() {
        return authFetch<AdminStats>('/api/admin/stats')
    }

    function fetchTenants(params: { page?: number; search?: string; status?: string } = {}) {
        const query = new URLSearchParams()
        if (params.page) query.set('page', String(params.page))
        if (params.search) query.set('search', params.search)
        if (params.status) query.set('status', params.status)
        const qs = query.toString()
        return authFetch<PaginatedTenants>(`/api/admin/tenants${qs ? `?${qs}` : ''}`)
    }

    function fetchPlans() {
        return authFetch<(Plan & { tenants_count: number })[]>('/api/admin/plans')
    }

    function updateTenantPlan(tenantId: number, planId: number) {
        return authFetch<{ message: string; tenant: Tenant }>(`/api/admin/tenants/${tenantId}/plan`, {
            method: 'PATCH',
            body: { plan_id: planId },
        })
    }

    function toggleTenantStatus(tenantId: number) {
        return authFetch<{ message: string; tenant: Tenant }>(`/api/admin/tenants/${tenantId}/toggle-status`, {
            method: 'PATCH',
        })
    }

    return {
        fetchStats,
        fetchTenants,
        fetchPlans,
        updateTenantPlan,
        toggleTenantStatus,
    }
}
