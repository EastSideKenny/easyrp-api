import { loadStripe, type Stripe } from '@stripe/stripe-js'

/** One Stripe.js client per browser tab — shared across all `useStripe()` callers */
let stripeSingleton: Stripe | null = null

/**
 * Composable for Stripe payment integration.
 * Handles creating payment methods, tokenizing cards, and subscription management.
 */
export function useStripe() {
    const config = useRuntimeConfig()
    const stripePublicKey = config.public.stripePublicKey as string

    const stripeLoading = ref(false)
    const stripeError = useState<string | null>('stripe.error', () => null)

    /**
     * Initialize Stripe (lazy load).
     */
    async function initStripe(): Promise<Stripe> {
        if (stripeSingleton) return stripeSingleton

        if (!stripePublicKey?.trim()) {
            stripeError.value = 'Stripe is not configured'
            throw new Error('Stripe is not configured')
        }

        stripeLoading.value = true
        stripeError.value = null

        try {
            stripeSingleton = await loadStripe(stripePublicKey)
            if (!stripeSingleton) {
                throw new Error('Failed to initialize Stripe')
            }
            return stripeSingleton
        } catch (err) {
            stripeError.value = 'Failed to load Stripe'
            throw err
        } finally {
            stripeLoading.value = false
        }
    }

    /**
     * Create a payment method from card details.
     * This should be called after user enters card info in a Stripe CardElement.
     *
     * @param cardElement - Stripe CardElement instance
     * @param billingDetails - Optional billing details
     * @returns paymentMethodId if successful, null otherwise
     */
    async function createPaymentMethod(
        cardElement: any,
        billingDetails?: {
            name?: string
            email?: string
            phone?: string
            address?: {
                line1?: string
                city?: string
                state?: string
                postal_code?: string
                country?: string
            }
        }
    ): Promise<string | null> {
        stripeError.value = null

        try {
            const stripe = await initStripe()

            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: billingDetails,
            })

            if (error) {
                stripeError.value = error.message || 'Card validation failed'
                return null
            }

            if (!paymentMethod?.id) {
                stripeError.value = 'Failed to create payment method'
                return null
            }

            return paymentMethod.id
        } catch (err: any) {
            stripeError.value = err.message || 'Payment method creation failed'
            return null
        }
    }

    return {
        initStripe,
        createPaymentMethod,
        stripeLoading,
        stripeError,
    }
}
