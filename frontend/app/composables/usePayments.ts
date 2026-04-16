import type { Payment, PaymentFormData, PaginatedResponse, PaginationMeta, ApiFilters } from '~/types'

export function usePayments() {
    const api = useApi()
    const payments = ref<Payment[]>([])
    const payment = ref<Payment | null>(null)
    const loading = ref(false)
    const meta = ref<PaginationMeta | null>(null)

    async function fetchPayments(filters: ApiFilters = {}) {
        loading.value = true
        try {
            const params = new URLSearchParams()
            Object.entries(filters).forEach(([key, val]) => {
                if (val !== undefined && val !== '') params.append(key, String(val))
            })
            const res = await api<PaginatedResponse<Payment> | Payment[]>(`/api/payments?${params}`)
            if (Array.isArray(res)) {
                payments.value = res
                meta.value = { current_page: 1, last_page: 1, per_page: res.length, total: res.length, from: 1, to: res.length }
            } else {
                payments.value = res.data
                meta.value = { current_page: res.current_page, last_page: res.last_page, per_page: res.per_page, total: res.total, from: res.from, to: res.to }
            }
        } catch (e) {
            console.error('Failed to fetch payments:', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchPayment(id: number) {
        loading.value = true
        try {
            payment.value = await api<Payment>(`/api/payments/${id}`)
        } finally {
            loading.value = false
        }
    }

    async function createPayment(data: PaymentFormData) {
        return await api<Payment>('/api/payments', { method: 'POST', body: data })
    }

    async function deletePayment(id: number) {
        await api(`/api/payments/${id}`, { method: 'DELETE' })
    }

    return {
        payments,
        payment,
        loading,
        meta,
        fetchPayments,
        fetchPayment,
        createPayment,
        deletePayment,
    }
}
