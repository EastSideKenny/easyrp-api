import type { WebshopSetting, WebshopSettingFormData } from '~/types'

export function useWebshopSettings() {
    const api = useApi()
    const settings = ref<WebshopSetting | null>(null)
    const loading = ref(false)
    const saving = ref(false)
    const errors = ref<Record<string, string>>({})

    async function fetchSettings() {
        loading.value = true
        try {
            settings.value = await api<WebshopSetting>('/api/webshop-settings')
        } catch (e) {
            console.error('Failed to fetch webshop settings:', e)
        } finally {
            loading.value = false
        }
    }

    async function updateSettings(data: WebshopSettingFormData) {
        saving.value = true
        errors.value = {}
        try {
            settings.value = await api<WebshopSetting>('/api/webshop-settings', {
                method: 'PATCH',
                body: data,
            })
            return true
        } catch (e: any) {
            if (e?.response?.status === 422 && e?.response?._data?.errors) {
                const fieldErrors: Record<string, string> = {}
                for (const [key, messages] of Object.entries(e.response._data.errors)) {
                    fieldErrors[key] = (messages as string[])[0] ?? 'Invalid'
                }
                errors.value = fieldErrors
            } else {
                console.error('Failed to update webshop settings:', e)
            }
            return false
        } finally {
            saving.value = false
        }
    }

    return {
        settings,
        loading,
        saving,
        errors,
        fetchSettings,
        updateSettings,
    }
}
