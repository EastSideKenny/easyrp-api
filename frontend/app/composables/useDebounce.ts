import { ref, watch } from 'vue'
import type { Ref } from 'vue'

/**
 * Debounce a ref value.
 */
export function useDebounce<T>(value: Ref<T>, delay = 300) {
    const debounced = ref(value.value) as Ref<T>
    let timeout: ReturnType<typeof setTimeout> | undefined

    watch(value, (newVal) => {
        clearTimeout(timeout)
        timeout = setTimeout(() => {
            debounced.value = newVal
        }, delay)
    })

    return debounced as Readonly<Ref<T>>
}
