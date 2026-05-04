import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: [],
  css: ['~/assets/css/main.css'],

  runtimeConfig: {
    public: {
      apiBaseUrl: '', // set via NUXT_PUBLIC_API_BASE_URL in .env (e.g. "http://localhost:8000")
      appDomain: '', // set via NUXT_PUBLIC_APP_DOMAIN in .env (e.g. "easyrp.com" or "localhost")
      stripePublicKey: 'pk_test_51TTGDwPoYI1vTyFW2lVSAV6HuhMSnVI23hWzLvQqUJidprmLBDRnQVUZcSAMrttFnRqCBVVEeVOF09J8Jxx7nPCX00Vokzgl8y' // NUXT_PUBLIC_STRIPE_PUBLISHABLE_KEY (pk_test_… / pk_live_…)
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
