/**
 * Format a number as currency.
 *
 * Workspace currency comes from the authenticated user's tenant when available,
 * otherwise from subdomain-resolved tenant — used as default when no ISO code is passed.
 *
 * Subscription plan amounts always pass an explicit currency from the API (Cashier billing).
 */
function localeForCurrency(currencyCode: string): string {
    const c = currencyCode.toUpperCase()
    const map: Record<string, string> = {
        EUR: 'de-DE',
        USD: 'en-US',
        GBP: 'en-GB',
        CHF: 'de-CH',
        JPY: 'ja-JP',
        AUD: 'en-AU',
        CAD: 'en-CA',
        NZD: 'en-NZ',
        SEK: 'sv-SE',
        NOK: 'nb-NO',
        DKK: 'da-DK',
        PLN: 'pl-PL',
        CZK: 'cs-CZ',
        INR: 'en-IN',
        ZAR: 'en-ZA',
        AED: 'ar-AE',
        MYR: 'ms-MY',
        SGD: 'en-SG',
        HKD: 'en-HK',
    }
    return map[c] ?? 'en-US'
}

export function useCurrency() {
    const { tenant } = useTenant()
    const { user } = useAuth()

    const tenantCurrency = computed(() =>
        (
            user.value?.tenant?.currency ||
            tenant.value?.currency ||
            'USD'
        ).toUpperCase(),
    )

    /**
     * @param currency ISO 4217 — omit to use workspace tenant currency
     * @param locale optional BCP 47 locale; defaults from currency code
     */
    const formatCurrency = (
        amount: number,
        currency?: string,
        locale?: string,
    ) => {
        const cur = (currency || tenantCurrency.value).toUpperCase()
        const loc = locale ?? localeForCurrency(cur)
        return new Intl.NumberFormat(loc, {
            style: 'currency',
            currency: cur,
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

    return {
        formatCurrency,
        formatNumber,
        formatPercentage,
        tenantCurrency,
        localeForCurrency,
    }
}
