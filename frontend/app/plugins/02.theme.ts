/**
 * Applies tenanttheme color overrides to CSS custom properties.
 * Watches the tenant state and updates root CSS variables whenever the theme changes.
 */
export default defineNuxtPlugin(() => {
    if (import.meta.server) return

    const { tenant } = useTenant()

    const themes: Record<string, { primary: string; light: string; dark: string }> = {
        default: { primary: '#4f46e5', light: '#818cf8', dark: '#3730a3' },
        blue: { primary: '#3b82f6', light: '#60a5fa', dark: '#1d4ed8' },
        green: { primary: '#16a34a', light: '#4ade80', dark: '#15803d' },
        purple: { primary: '#9333ea', light: '#c084fc', dark: '#7e22ce' },
        rose: { primary: '#e11d48', light: '#fb7185', dark: '#be123c' },
        orange: { primary: '#ea580c', light: '#fb923c', dark: '#c2410c' },
    }

    function applyTheme(themeName: string | null | undefined) {
        const t = themes[themeName ?? 'default'] ?? themes.default
        const root = document.documentElement.style
        root.setProperty('--color-primary', t.primary)
        root.setProperty('--color-primary-light', t.light)
        root.setProperty('--color-primary-dark', t.dark)
    }

    // Apply immediately if tenant is already resolved
    applyTheme(tenant.value?.theme)

    // Watch for changes (e.g. tenant resolves after plugin runs, or theme is updated)
    watch(() => tenant.value?.theme, (theme) => {
        applyTheme(theme)
    })
})
