/**
 * Format a number as currency.
 *
 * The tenant's currency is read from global state so every call to
 * formatCurrency() automatically uses the right symbol without needing
 * to pass it explicitly.
 */
export function useCurrency() {
    // Read the current tenant's currency from the shared Nuxt state.
    // Falls back to USD if the tenant hasn't loaded yet or has no currency set.
    const tenant = useState<{ currency?: string | null } | null>('current-tenant', () => null)
    const tenantCurrency = computed(() => tenant.value?.currency || 'USD')

    const formatCurrency = (amount: number, currency?: string, locale = 'en-US') => {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency || tenantCurrency.value,
            minimumFractionDigits: 2,
        }).format(amount)
    }

    const formatNumber = (value: number, locale = 'en-US') => {
        return new Intl.NumberFormat(locale).format(value)
    }

    const formatPercentage = (value: number, locale = 'en-US') => {
        return new Intl.NumberFormat(locale, {
            style: 'percent',
            minimumFractionDigits: 1,
        }).format(value / 100)
    }

    return { formatCurrency, formatNumber, formatPercentage, tenantCurrency }
}
