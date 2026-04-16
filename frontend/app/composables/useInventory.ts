import type { Product, StockMovement, StockMovementFormData, PaginatedResponse, PaginationMeta, ApiFilters } from '~/types'

/**
 * Shape returned by the /api/inventory endpoint.
 */
interface InventoryResponse {
    summary: {
        total_skus: number
        total_units: number
        low_stock: number
        out_of_stock: number
    }
    items: {
        id: number
        product: string
        sku: string
        type: string
        qty: number
        threshold: number
        cost: number
        value: number
        status: string
    }[]
}

export function useInventory() {
    const api = useApi()
    const stockItems = ref<Product[]>([])
    const movements = ref<StockMovement[]>([])
    const loading = ref(false)
    const meta = ref<PaginationMeta | null>(null)

    async function fetchStock(filters: ApiFilters = {}) {
        loading.value = true
        try {
            const params = new URLSearchParams()
            Object.entries(filters).forEach(([key, val]) => {
                if (val !== undefined && val !== '') params.append(key, String(val))
            })
            const res = await api<InventoryResponse | PaginatedResponse<Product> | Product[]>(`/api/inventory?${params}`)

            if (Array.isArray(res)) {
                // Plain array of Product objects
                stockItems.value = res
                meta.value = { current_page: 1, last_page: 1, per_page: res.length, total: res.length, from: 1, to: res.length }
            } else if ('items' in res && 'summary' in res) {
                // { summary, items } shape from the API
                const inv = res as InventoryResponse
                stockItems.value = inv.items.map((i) => ({
                    id: i.id,
                    tenant_id: 0,
                    name: i.product,
                    description: '',
                    sku: i.sku,
                    type: i.type as Product['type'],
                    price: 0,
                    cost_price: i.cost,
                    tax_rate: 0,
                    track_inventory: true,
                    stock_quantity: i.qty,
                    low_stock_threshold: i.threshold,
                    is_active: true,
                    created_at: '',
                    updated_at: '',
                }))
                const count = inv.items.length
                meta.value = { current_page: 1, last_page: 1, per_page: count, total: count, from: 1, to: count }
            } else if ('data' in res && 'current_page' in res) {
                // Standard paginated response (Laravel flat format)
                const paginated = res as PaginatedResponse<Product>
                stockItems.value = paginated.data
                meta.value = { current_page: paginated.current_page, last_page: paginated.last_page, per_page: paginated.per_page, total: paginated.total, from: paginated.from, to: paginated.to }
            }
        } catch (e) {
            console.error('Failed to fetch inventory:', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchMovements(productId?: number) {
        loading.value = true
        try {
            const url = productId
                ? `/api/stock-movements?product_id=${productId}`
                : '/api/stock-movements'
            const res = await api<PaginatedResponse<StockMovement> | StockMovement[]>(url)
            movements.value = Array.isArray(res) ? res : res.data
        } catch (e) {
            console.error('Failed to fetch stock movements:', e)
        } finally {
            loading.value = false
        }
    }

    async function createMovement(data: StockMovementFormData) {
        return await api<StockMovement>('/api/stock-movements', {
            method: 'POST',
            body: data,
        })
    }

    return {
        stockItems,
        movements,
        loading,
        meta,
        fetchStock,
        fetchMovements,
        createMovement,
    }
}
