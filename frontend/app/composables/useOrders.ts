import type { Order, OrderFormData, PaginatedResponse, PaginationMeta, ApiFilters } from '~/types'

function refreshUsage() {
    if (!import.meta.client) return
    const { authFetch } = useAuth()
    useSubscription().fetchSubscription(authFetch).catch(() => { })
}

export function useOrders() {
    const api = useApi()
    const orders = ref<Order[]>([])
    const order = ref<Order | null>(null)
    const loading = ref(false)
    const meta = ref<PaginationMeta | null>(null)

    async function fetchOrders(filters: ApiFilters = {}) {
        loading.value = true
        try {
            const params = new URLSearchParams()
            Object.entries(filters).forEach(([key, val]) => {
                if (val !== undefined && val !== '') params.append(key, String(val))
            })
            const res = await api<PaginatedResponse<Order> | Order[]>(`/api/orders?${params}`)
            if (Array.isArray(res)) {
                orders.value = res
                meta.value = { current_page: 1, last_page: 1, per_page: res.length, total: res.length, from: 1, to: res.length }
            } else {
                orders.value = res.data
                meta.value = { current_page: res.current_page, last_page: res.last_page, per_page: res.per_page, total: res.total, from: res.from, to: res.to }
            }
        } catch (e) {
            console.error('Failed to fetch orders:', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchOrder(id: number) {
        loading.value = true
        try {
            order.value = await api<Order>(`/api/orders/${id}`)
        } catch (e) {
            console.error('Failed to fetch order:', e)
        } finally {
            loading.value = false
        }
    }

    async function createOrder(data: OrderFormData & { subtotal: number; tax_total: number; total: number }) {
        const result = await api<Order>('/api/orders', {
            method: 'POST',
            body: data,
        })
        refreshUsage()
        return result
    }

    async function updateOrder(id: number, data: Partial<Order> | (OrderFormData & { subtotal: number; tax_total: number; total: number })) {
        return await api<Order>(`/api/orders/${id}`, {
            method: 'PATCH',
            body: data,
        })
    }

    return {
        orders,
        order,
        loading,
        meta,
        fetchOrders,
        fetchOrder,
        createOrder,
        updateOrder,
    }
}
