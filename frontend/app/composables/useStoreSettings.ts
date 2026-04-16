import type { WebshopSetting } from '~/types'

/**
 * Public-facing composable that loads the tenant's webshop settings
 * for the storefront. Uses the unauthenticated /storefront/{subdomain}
 * routes so no login is required. Data is cached via useState.
 */
export function useStoreSettings() {
    const config = useRuntimeConfig()
    const baseUrl = config.public.apiBaseUrl as string
    const { tenantSlug } = useTenant()

    const settings = useState<WebshopSetting | null>('store-settings', () => null)
    const loaded = useState<boolean>('store-settings-loaded', () => false)

    async function loadSettings() {
        if (loaded.value) return
        const slug = tenantSlug.value
        if (!slug) {
            console.warn('[StoreSettings] No tenant slug available')
            loaded.value = true
            return
        }
        try {
            settings.value = await $fetch<WebshopSetting>(`/api/storefront/${slug}/settings`, {
                baseURL: baseUrl,
                headers: { Accept: 'application/json' },
            })
        } catch (e) {
            console.error('Failed to load store settings:', e)
        } finally {
            loaded.value = true
        }
    }

    const storeName = computed(() => settings.value?.store_name || 'Store')
    const primaryColor = computed(() => settings.value?.primary_color || '#4f46e5')
    const currency = computed(() => settings.value?.currency || 'USD')
    const guestCheckoutEnabled = computed(() => settings.value?.enable_guest_checkout ?? true)

    /**
     * Apply the tenant's primary colour as CSS custom properties on a
     * given element (usually the store layout root). This overrides
     * the Tailwind theme tokens for the store only.
     */
    function applyTheme(el: HTMLElement | null) {
        if (!el) return

        const hex = primaryColor.value
        el.style.setProperty('--color-primary', hex)

        // Derive a lighter tint (for gradients / badges)
        el.style.setProperty('--color-primary-light', adjustBrightness(hex, 40))
        // Derive a darker shade (for hover states)
        el.style.setProperty('--color-primary-dark', adjustBrightness(hex, -30))
    }

    return {
        settings,
        loaded,
        loadSettings,
        storeName,
        primaryColor,
        currency,
        guestCheckoutEnabled,
        applyTheme,
    }
}

/** Lighten (+) or darken (-) a hex colour by a percentage amount. */
function adjustBrightness(hex: string, percent: number): string {
    const num = parseInt(hex.replace('#', ''), 16)
    const r = Math.min(255, Math.max(0, (num >> 16) + Math.round(2.55 * percent)))
    const g = Math.min(255, Math.max(0, ((num >> 8) & 0x00ff) + Math.round(2.55 * percent)))
    const b = Math.min(255, Math.max(0, (num & 0x0000ff) + Math.round(2.55 * percent)))
    return `#${(r << 16 | g << 8 | b).toString(16).padStart(6, '0')}`
}
