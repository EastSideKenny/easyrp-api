import { ref } from 'vue'

export type ToastVariant = 'success' | 'error' | 'warning' | 'info'

export interface Toast {
    id: number
    message: string
    variant: ToastVariant
    duration: number
}

let nextId = 0

/**
 * Extract a user-friendly message from a backend error response.
 */
function extractErrorMessage(e: any, fallback = 'Something went wrong. Please try again.'): string {
    // Laravel validation: { message: '...' }
    const msg = e?.data?.message ?? e?.response?._data?.message
    if (msg && typeof msg === 'string') return msg

    // Plain string
    if (typeof e === 'string') return e

    // HTTP status text
    if (e?.statusMessage) return e.statusMessage

    return fallback
}

// Shared reactive array — lives outside Nuxt's SSR payload so it
// works reliably inside setTimeout / async callbacks without needing
// a Nuxt instance context.
const toasts = ref<Toast[]>([])

export function useToast() {
    function add(message: string, variant: ToastVariant = 'info', duration = 4000) {
        const id = ++nextId
        toasts.value = [...toasts.value, { id, message, variant, duration }]

        if (duration > 0 && import.meta.client) {
            setTimeout(() => remove(id), duration)
        }
    }

    function remove(id: number) {
        toasts.value = toasts.value.filter((t) => t.id !== id)
    }

    function success(message: string, duration?: number) {
        add(message, 'success', duration)
    }

    function error(message: string, duration?: number) {
        add(message, 'error', duration ?? 5000)
    }

    function warning(message: string, duration?: number) {
        add(message, 'warning', duration)
    }

    function info(message: string, duration?: number) {
        add(message, 'info', duration)
    }

    /** Show a toast from a caught backend error */
    function apiError(e: any, fallback?: string) {
        error(extractErrorMessage(e, fallback))
    }

    return { toasts, add, remove, success, error, warning, info, apiError }
}
