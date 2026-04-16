<template>
  <div class="min-h-screen bg-surface-alt font-sans text-text">
    <!-- ─── Navigation ─── -->
    <header
      class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
      :class="
        scrolled
          ? 'bg-white/80 backdrop-blur-xl border-b border-border shadow-xs'
          : 'bg-transparent'
      "
    >
      <div
        class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between"
      >
        <NuxtLink to="/" class="flex items-center gap-2.5">
          <div
            class="w-8 h-8 rounded-xl bg-linear-to-br from-primary to-primary-light flex items-center justify-center"
          >
            <span class="text-white text-sm font-bold">E</span>
          </div>
          <span class="font-semibold text-base tracking-tight text-text"
            >EasyRP</span
          >
        </NuxtLink>

        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-1">
          <a
            href="#features"
            class="px-3 py-1.5 text-sm text-text-secondary rounded-lg hover:bg-surface-alt hover:text-text transition-all duration-200"
            >Features</a
          >
          <a
            href="#pricing"
            class="px-3 py-1.5 text-sm text-text-secondary rounded-lg hover:bg-surface-alt hover:text-text transition-all duration-200"
            >Pricing</a
          >
          <a
            href="#testimonials"
            class="px-3 py-1.5 text-sm text-text-secondary rounded-lg hover:bg-surface-alt hover:text-text transition-all duration-200"
            >Testimonials</a
          >
          <NuxtLink
            to="/auth/login"
            class="ml-2 px-3 py-1.5 text-sm text-text-secondary rounded-lg hover:bg-surface-alt hover:text-text transition-all duration-200"
          >
            Sign in
          </NuxtLink>
          <NuxtLink
            to="/auth/register"
            class="ml-1 bg-primary text-white text-sm font-medium px-4 py-2 rounded-xl hover:bg-primary-dark hover:shadow-elevated transition-all duration-200"
          >
            Get started free
          </NuxtLink>
        </nav>

        <!-- Mobile menu toggle -->
        <button
          class="md:hidden p-2 rounded-lg hover:bg-surface-alt transition"
          @click="mobileOpen = !mobileOpen"
        >
          <svg
            v-if="!mobileOpen"
            class="w-5 h-5 text-text"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
          <svg
            v-else
            class="w-5 h-5 text-text"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Mobile menu -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="mobileOpen"
          class="md:hidden bg-white border-b border-border px-6 pb-4 space-y-1"
        >
          <a
            href="#features"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Features</a
          >
          <a
            href="#pricing"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Pricing</a
          >
          <a
            href="#testimonials"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Testimonials</a
          >
          <hr class="border-border my-2" />
          <NuxtLink
            to="/auth/login"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Sign in</NuxtLink
          >
          <NuxtLink
            to="/auth/register"
            class="block px-3 py-2 text-sm font-medium text-primary rounded-lg hover:bg-primary/5"
            @click="mobileOpen = false"
            >Get started free →</NuxtLink
          >
        </div>
      </Transition>
    </header>

    <!-- Page content -->
    <main>
      <slot />
    </main>

    <!-- ─── Footer ─── -->
    <footer class="bg-text text-white">
      <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
          <!-- Brand -->
          <div class="col-span-2 md:col-span-1">
            <div class="flex items-center gap-2.5 mb-4">
              <div
                class="w-8 h-8 rounded-xl bg-linear-to-br from-primary to-primary-light flex items-center justify-center"
              >
                <span class="text-white text-sm font-bold">E</span>
              </div>
              <span class="font-semibold text-base tracking-tight">EasyRP</span>
            </div>
            <p class="text-sm text-white/60 leading-relaxed">
              The modern ERP platform for growing businesses. Invoicing,
              inventory, and online store — all in one place.
            </p>
          </div>

          <!-- Product -->
          <div>
            <h4
              class="text-xs font-semibold uppercase tracking-widest text-white/40 mb-4"
            >
              Product
            </h4>
            <ul class="space-y-2.5">
              <li>
                <a
                  href="#features"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Features</a
                >
              </li>
              <li>
                <a
                  href="#pricing"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Pricing</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Integrations</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Changelog</a
                >
              </li>
            </ul>
          </div>

          <!-- Company -->
          <div>
            <h4
              class="text-xs font-semibold uppercase tracking-widest text-white/40 mb-4"
            >
              Company
            </h4>
            <ul class="space-y-2.5">
              <li>
                <a
                  href="#"
                  class="text-sm text-white/60 hover:text-white transition"
                  >About</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Blog</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Careers</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Contact</a
                >
              </li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <h4
              class="text-xs font-semibold uppercase tracking-widest text-white/40 mb-4"
            >
              Legal
            </h4>
            <ul class="space-y-2.5">
              <li>
                <a
                  href="#"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Privacy Policy</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Terms of Service</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Security</a
                >
              </li>
            </ul>
          </div>
        </div>

        <div
          class="border-t border-white/10 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4"
        >
          <p class="text-sm text-white/40">
            © {{ new Date().getFullYear() }} EasyRP. All rights reserved.
          </p>
          <div class="flex items-center gap-4">
            <!-- Twitter/X -->
            <a
              href="#"
              class="text-white/40 hover:text-white transition"
              aria-label="Twitter"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
                />
              </svg>
            </a>
            <!-- GitHub -->
            <a
              href="#"
              class="text-white/40 hover:text-white transition"
              aria-label="GitHub"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                />
              </svg>
            </a>
            <!-- LinkedIn -->
            <a
              href="#"
              class="text-white/40 hover:text-white transition"
              aria-label="LinkedIn"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"
                />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
const mobileOpen = ref(false);
const scrolled = ref(false);

onMounted(() => {
  const onScroll = () => {
    scrolled.value = window.scrollY > 20;
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onUnmounted(() => window.removeEventListener("scroll", onScroll));
});
</script>
