import tailwindcss from '@tailwindcss/vite'

const defaultDescription =
  'EasyRP is quote to invoice automation software for freelancers and small businesses — send quotes online, capture acceptance by email, and generate invoices automatically.'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: process.env.NODE_ENV === 'development' },
  modules: [],
  css: ['~/assets/css/main.css'],

  ssr: true,

  routeRules: {
    '/': { prerender: true },
    '/quote-to-invoice-software': { prerender: true },
    '/invoice-automation': { prerender: true },
    '/freelance-invoicing-tool': { prerender: true },
    '/send-quotes-online': { prerender: true },
  },

  app: {
    head: {
      htmlAttrs: { lang: 'en' },
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1',
      title: 'EasyRP',
      titleTemplate: (titleChunk) =>
        titleChunk && titleChunk !== 'EasyRP' ? `${titleChunk} · EasyRP` : 'EasyRP',
      meta: [
        { name: 'description', content: defaultDescription },
        { name: 'theme-color', content: '#4f46e5' },
        { name: 'format-detection', content: 'telephone=no' },
      ],
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
        {
          rel: 'icon',
          type: 'image/png',
          sizes: '32x32',
          href: '/favicon-32x32.png',
        },
        {
          rel: 'icon',
          type: 'image/png',
          sizes: '16x16',
          href: '/favicon-16x16.png',
        },
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' },
      ],
    },
  },

  runtimeConfig: {
    public: {
      apiBaseUrl:
        import.meta.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost:8000',
      appDomain: import.meta.env.NUXT_PUBLIC_APP_DOMAIN || 'localhost',
      siteUrl: import.meta.env.NUXT_PUBLIC_SITE_URL ?? "",
      googleAnalyticsMeasurementId:
        import.meta.env.NUXT_PUBLIC_GA_MEASUREMENT_ID ?? "",
      googleSearchConsoleVerification:
        import.meta.env.NUXT_PUBLIC_GSC_VERIFICATION ?? "",
      stripePublicKey:
        import.meta.env.NUXT_PUBLIC_STRIPE_PUBLISHABLE_KEY ?? "",
    },
  },

  vite: {
    plugins: [tailwindcss()],
    optimizeDeps: {
      include: [
        '@vue/devtools-core',
        '@vue/devtools-kit',
      ]
    },
  },
})
