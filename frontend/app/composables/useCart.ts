import type { CartItem, Product } from '~/types'

const CART_KEY = 'easyrp_cart'

export function useCart() {
    const items = useState<CartItem[]>('cart-items', () => {
        if (import.meta.client) {
            const saved = localStorage.getItem(CART_KEY)
            return saved ? JSON.parse(saved) : []
        }
        return []
    })

    // Persist to localStorage
    watch(items, (val) => {
        if (import.meta.client) {
            localStorage.setItem(CART_KEY, JSON.stringify(val))
        }
    }, { deep: true })

    const itemCount = computed(() =>
        items.value.reduce((sum, item) => sum + item.quantity, 0)
    )

    const subtotal = computed(() =>
        items.value.reduce((sum, item) => sum + item.product.price * item.quantity, 0)
    )

    function addItem(product: Product, quantity = 1) {
        const existing = items.value.find(i => i.product.id === product.id)
        if (existing) {
            existing.quantity += quantity
        } else {
            items.value.push({ product, quantity })
        }
    }

    function updateQuantity(productId: number, quantity: number) {
        const item = items.value.find(i => i.product.id === productId)
        if (item) {
            if (quantity <= 0) {
                removeItem(productId)
            } else {
                item.quantity = quantity
            }
        }
    }

    function removeItem(productId: number) {
        items.value = items.value.filter(i => i.product.id !== productId)
    }

    function clearCart() {
        items.value = []
        if (import.meta.client) {
            localStorage.removeItem(CART_KEY)
        }
    }

    return {
        items,
        itemCount,
        subtotal,
        addItem,
        updateQuantity,
        removeItem,
        clearCart,
    }
}
