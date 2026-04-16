import type { RevenueReport, SalesByProductReport, StockValueReport } from '~/types'

export function useReports() {
    const api = useApi()
    const loading = ref(false)

    async function fetchRevenueReport(params: { from?: string; to?: string; group_by?: 'day' | 'week' | 'month' } = {}) {
        loading.value = true
        try {
            const query = new URLSearchParams()
            Object.entries(params).forEach(([key, val]) => {
                if (val) query.append(key, val)
            })
            return await api<RevenueReport[]>(`/api/reports/revenue?${query}`)
        } finally {
            loading.value = false
        }
    }

    async function fetchSalesByProduct(params: { from?: string; to?: string; limit?: number } = {}) {
        loading.value = true
        try {
            const query = new URLSearchParams()
            Object.entries(params).forEach(([key, val]) => {
                if (val !== undefined) query.append(key, String(val))
            })
            return await api<SalesByProductReport[]>(`/api/reports/sales-by-product?${query}`)
        } finally {
            loading.value = false
        }
    }

    async function fetchStockValueReport() {
        loading.value = true
        try {
            return await api<StockValueReport[]>('/api/reports/stock-value')
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        fetchRevenueReport,
        fetchSalesByProduct,
        fetchStockValueReport,
    }
}
