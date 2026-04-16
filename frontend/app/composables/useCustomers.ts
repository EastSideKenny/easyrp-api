import type { Customer, CustomerFormData, PaginatedResponse, PaginationMeta, ApiFilters } from '~/types'

function refreshUsage() {
    if (!import.meta.client) return
    const { authFetch } = useAuth()
    useSubscription().fetchSubscription(authFetch).catch(() => { })
}

export function useCustomers() {
    const api = useApi()
    const customers = ref<Customer[]>([])
    const customer = ref<Customer | null>(null)
    const loading = ref(false)
    const meta = ref<PaginationMeta | null>(null)

    async function fetchCustomers(filters: ApiFilters = {}) {
        loading.value = true
        try {
            const params = new URLSearchParams()
            Object.entries(filters).forEach(([key, val]) => {
                if (val !== undefined && val !== '') params.append(key, String(val))
            })
            const res = await api<PaginatedResponse<Customer> | Customer[]>(`/api/customers?${params}`)
            if (Array.isArray(res)) {
                customers.value = res
                meta.value = { current_page: 1, last_page: 1, per_page: res.length, total: res.length, from: 1, to: res.length }
            } else {
                customers.value = res.data
                meta.value = { current_page: res.current_page, last_page: res.last_page, per_page: res.per_page, total: res.total, from: res.from, to: res.to }
            }
        } catch (e) {
            console.error('Failed to fetch customers:', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchCustomer(id: number) {
        loading.value = true
        try {
            customer.value = await api<Customer>(`/api/customers/${id}`)
        } finally {
            loading.value = false
        }
    }

    async function createCustomer(data: CustomerFormData) {
        const result = await api<Customer>('/api/customers', { method: 'POST', body: data })
        refreshUsage()
        return result
    }

    async function updateCustomer(id: number, data: Partial<CustomerFormData>) {
        return await api<Customer>(`/api/customers/${id}`, { method: 'PUT', body: data })
    }

    async function deleteCustomer(id: number) {
        await api(`/api/customers/${id}`, { method: 'DELETE' })
        refreshUsage()
    }

    return {
        customers,
        customer,
        loading,
        meta,
        fetchCustomers,
        fetchCustomer,
        createCustomer,
        updateCustomer,
        deleteCustomer,
    }
}
