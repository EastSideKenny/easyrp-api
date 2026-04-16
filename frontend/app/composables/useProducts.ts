import type { Product, ProductFormData, PaginatedResponse, PaginationMeta, ApiFilters } from '~/types'

function refreshUsage() {
    if (!import.meta.client) return
    const { authFetch } = useAuth()
    useSubscription().fetchSubscription(authFetch).catch(() => { })
}

export function useProducts() {
    const api = useApi()
    const products = ref<Product[]>([])
    const product = ref<Product | null>(null)
    const loading = ref(false)
    const meta = ref<PaginationMeta | null>(null)

    async function fetchProducts(filters: ApiFilters = {}) {
        loading.value = true
        try {
            const params = new URLSearchParams()
            Object.entries(filters).forEach(([key, val]) => {
                if (val !== undefined && val !== '') params.append(key, String(val))
            })
            const res = await api<PaginatedResponse<Product> | Product[]>(`/api/products?${params}`)
            if (Array.isArray(res)) {
                products.value = res
                meta.value = { current_page: 1, last_page: 1, per_page: res.length, total: res.length, from: 1, to: res.length }
            } else {
                products.value = res.data
                meta.value = { current_page: res.current_page, last_page: res.last_page, per_page: res.per_page, total: res.total, from: res.from, to: res.to }
            }
        } catch (e) {
            console.error('Failed to fetch products:', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchProduct(id: number) {
        loading.value = true
        try {
            product.value = await api<Product>(`/api/products/${id}`)
        } finally {
            loading.value = false
        }
    }

    async function createProduct(data: ProductFormData) {
        const result = await api<Product>('/api/products', { method: 'POST', body: data })
        refreshUsage()
        return result
    }

    async function updateProduct(id: number, data: Partial<ProductFormData>) {
        return await api<Product>(`/api/products/${id}`, { method: 'PUT', body: data })
    }

    async function deleteProduct(id: number) {
        await api(`/api/products/${id}`, { method: 'DELETE' })
        refreshUsage()
    }

    return {
        products,
        product,
        loading,
        meta,
        fetchProducts,
        fetchProduct,
        createProduct,
        updateProduct,
        deleteProduct,
    }
}
