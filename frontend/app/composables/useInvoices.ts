import type { Invoice, InvoiceFormData, PaginatedResponse, PaginationMeta, ApiFilters } from '~/types'

function refreshUsage() {
    if (!import.meta.client) return
    const { authFetch } = useAuth()
    useSubscription().fetchSubscription(authFetch).catch(() => { })
}

export function useInvoices() {
    const api = useApi()
    const invoices = ref<Invoice[]>([])
    const invoice = ref<Invoice | null>(null)
    const loading = ref(false)
    const meta = ref<PaginationMeta | null>(null)

    async function fetchInvoices(filters: ApiFilters = {}) {
        loading.value = true
        try {
            const params = new URLSearchParams()
            Object.entries(filters).forEach(([key, val]) => {
                if (val !== undefined && val !== '') params.append(key, String(val))
            })
            const res = await api<PaginatedResponse<Invoice> | Invoice[]>(`/api/invoices?${params}`)
            if (Array.isArray(res)) {
                invoices.value = res
                meta.value = { current_page: 1, last_page: 1, per_page: res.length, total: res.length, from: 1, to: res.length }
            } else {
                invoices.value = res.data
                meta.value = { current_page: res.current_page, last_page: res.last_page, per_page: res.per_page, total: res.total, from: res.from, to: res.to }
            }
        } catch (e) {
            console.error('Failed to fetch invoices:', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchInvoice(id: number) {
        loading.value = true
        try {
            invoice.value = await api<Invoice>(`/api/invoices/${id}`)
        } finally {
            loading.value = false
        }
    }

    async function createInvoice(data: InvoiceFormData) {
        const result = await api<Invoice>('/api/invoices', { method: 'POST', body: data })
        refreshUsage()
        return result
    }

    async function updateInvoice(id: number, data: Partial<InvoiceFormData>) {
        return await api<Invoice>(`/api/invoices/${id}`, { method: 'PUT', body: data })
    }

    async function deleteInvoice(id: number) {
        await api(`/api/invoices/${id}`, { method: 'DELETE' })
        refreshUsage()
    }

    async function sendInvoice(id: number) {
        return await api<Invoice>(`/api/invoices/${id}/send`, { method: 'POST' })
    }

    async function markAsPaid(id: number) {
        return await api<Invoice>(`/api/invoices/${id}/mark-paid`, { method: 'POST' })
    }

    async function downloadInvoicePdf(id: number) {
        const blob = await api<Blob>(`/api/invoices/${id}/pdf`, { responseType: 'blob' })
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `invoice-${id}.pdf`
        document.body.appendChild(a)
        a.click()
        a.remove()
        URL.revokeObjectURL(url)
    }

    return {
        invoices,
        invoice,
        loading,
        meta,
        fetchInvoices,
        fetchInvoice,
        createInvoice,
        updateInvoice,
        deleteInvoice,
        sendInvoice,
        markAsPaid,
        downloadInvoicePdf,
    }
}
