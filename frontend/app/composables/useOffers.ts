import type { Offer, OfferFormData, PaginatedResponse, PaginationMeta, ApiFilters } from '~/types'

function refreshUsage() {
    if (!import.meta.client) return
    const { authFetch } = useAuth()
    useSubscription().fetchSubscription(authFetch).catch(() => { })
}

export function useOffers() {
    const api = useApi()
    const offers = ref<Offer[]>([])
    const offer = ref<Offer | null>(null)
    const loading = ref(false)
    const meta = ref<PaginationMeta | null>(null)

    async function fetchOffers(filters: ApiFilters = {}) {
        loading.value = true
        try {
            const params = new URLSearchParams()
            Object.entries(filters).forEach(([key, val]) => {
                if (val !== undefined && val !== '') params.append(key, String(val))
            })
            const res = await api<PaginatedResponse<Offer> | Offer[]>(`/api/offers?${params}`)
            if (Array.isArray(res)) {
                offers.value = res
                meta.value = { current_page: 1, last_page: 1, per_page: res.length, total: res.length, from: 1, to: res.length }
            } else {
                offers.value = res.data
                meta.value = { current_page: res.current_page, last_page: res.last_page, per_page: res.per_page, total: res.total, from: res.from, to: res.to }
            }
        } catch (e) {
            console.error('Failed to fetch offers:', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchOffer(id: number) {
        loading.value = true
        try {
            offer.value = await api<Offer>(`/api/offers/${id}`)
        } finally {
            loading.value = false
        }
    }

    async function createOffer(data: OfferFormData) {
        const result = await api<Offer>('/api/offers', { method: 'POST', body: data })
        refreshUsage()
        return result
    }

    async function updateOffer(id: number, data: Partial<OfferFormData>) {
        return await api<Offer>(`/api/offers/${id}`, { method: 'PUT', body: data })
    }

    async function deleteOffer(id: number) {
        await api(`/api/offers/${id}`, { method: 'DELETE' })
        refreshUsage()
    }

    /** Send the offer to the customer (e.g. via email). Backend sets status → sent. */
    async function sendOffer(id: number) {
        return await api<Offer>(`/api/offers/${id}/send`, { method: 'POST' })
    }

    /** Accept the offer — backend creates a draft invoice and returns the updated offer. */
    async function acceptOffer(id: number) {
        return await api<Offer>(`/api/offers/${id}/accept`, { method: 'POST' })
    }

    /** Decline the offer. */
    async function declineOffer(id: number) {
        return await api<Offer>(`/api/offers/${id}/decline`, { method: 'POST' })
    }

    /** Download the PDF for this offer via the authenticated API endpoint. */
    async function downloadOfferPdf(id: number) {
        const blob = await api<Blob>(`/api/offers/${id}/pdf`, { responseType: 'blob' })
        const filename = offer.value?.offer_number
            ? `${offer.value.offer_number}.pdf`
            : `offer-${id}.pdf`
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = filename
        a.click()
        URL.revokeObjectURL(url)
    }

    return {
        offers,
        offer,
        loading,
        meta,
        fetchOffers,
        fetchOffer,
        createOffer,
        updateOffer,
        deleteOffer,
        sendOffer,
        acceptOffer,
        declineOffer,
        downloadOfferPdf,
    }
}
