<template>
  <div class="marketing-shell min-h-screen font-sans text-text">
    <!-- ─── Navigation ─── -->
    <header
      class="sticky top-0 inset-x-0 z-50 transition-all duration-300"
      :class="
        scrolled || mobileOpen
          ? 'bg-white/90 backdrop-blur-md border-b border-border/70'
          : 'bg-transparent'
      "
    >
      <div
        class="marketing-layer max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between"
      >
        <NuxtLink to="/" class="flex items-center gap-2.5">
          <div
            class="w-8 h-8 rounded-xl marketing-cta flex items-center justify-center shadow-md"
          >
            <span class="text-white text-sm font-bold">E</span>
          </div>
          <span class="font-semibold text-base tracking-tight text-text"
            >EasyRP</span
          >
        </NuxtLink>

        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-1 rounded-2xl px-2 py-1 marketing-pill-nav" aria-label="Primary">
          <a
            href="/#features"
            class="px-3 py-1.5 text-sm text-text-secondary rounded-lg hover:bg-surface-alt hover:text-text transition-all duration-200"
            >Features</a
          >
          <a
            href="/#pricing"
            class="px-3 py-1.5 text-sm text-text-secondary rounded-lg hover:bg-surface-alt hover:text-text transition-all duration-200"
            >Pricing</a
          >
          <a
            href="/#faq"
            class="px-3 py-1.5 text-sm text-text-secondary rounded-lg hover:bg-surface-alt hover:text-text transition-all duration-200"
            >FAQ</a
          >
          <div class="relative group">
            <button
              type="button"
              class="px-3 py-1.5 text-sm text-text-secondary rounded-lg hover:bg-surface-alt hover:text-text transition-all duration-200 inline-flex items-center gap-1"
            >
              Guides
              <svg
                class="w-3.5 h-3.5 opacity-60"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>
            <div
              class="absolute left-0 top-full pt-2 w-60 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity z-50"
            >
              <div
                class="rounded-xl border border-border bg-white shadow-elevated py-2 text-sm"
              >
                <NuxtLink
                  to="/quote-to-invoice-software"
                  class="block px-4 py-2 text-text-secondary hover:bg-surface-alt hover:text-text"
                  >Quote to invoice software</NuxtLink
                >
                <NuxtLink
                  to="/invoice-automation"
                  class="block px-4 py-2 text-text-secondary hover:bg-surface-alt hover:text-text"
                  >Invoice automation</NuxtLink
                >
                <NuxtLink
                  to="/freelance-invoicing-tool"
                  class="block px-4 py-2 text-text-secondary hover:bg-surface-alt hover:text-text"
                  >Freelance invoicing tool</NuxtLink
                >
                <NuxtLink
                  to="/send-quotes-online"
                  class="block px-4 py-2 text-text-secondary hover:bg-surface-alt hover:text-text"
                  >Send quotes online</NuxtLink
                >
              </div>
            </div>
          </div>
          <NuxtLink
            to="/auth/login"
            class="ml-2 px-3 py-1.5 text-sm text-text-secondary rounded-lg hover:bg-surface-alt hover:text-text transition-all duration-200"
          >
            Sign in
          </NuxtLink>
          <NuxtLink
            to="/auth/register"
            class="ml-1 marketing-cta text-sm font-medium px-4 py-2 rounded-xl hover:shadow-elevated transition-all duration-200"
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
          class="md:hidden marketing-pill-nav border-b border-border/80 px-6 pb-4 space-y-1"
        >
          <a
            href="/#features"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Features</a
          >
          <a
            href="/#pricing"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Pricing</a
          >
          <a
            href="/#faq"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >FAQ</a
          >
          <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-text-muted">
            Guides
          </p>
          <NuxtLink
            to="/quote-to-invoice-software"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Quote to invoice software</NuxtLink
          >
          <NuxtLink
            to="/invoice-automation"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Invoice automation</NuxtLink
          >
          <NuxtLink
            to="/freelance-invoicing-tool"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Freelance invoicing tool</NuxtLink
          >
          <NuxtLink
            to="/send-quotes-online"
            class="block px-3 py-2 text-sm text-text-secondary rounded-lg hover:bg-surface-alt"
            @click="mobileOpen = false"
            >Send quotes online</NuxtLink
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
            class="block px-3 py-2 text-sm font-medium text-primary rounded-lg marketing-subtle-outline"
            @click="mobileOpen = false"
            >Get started free →</NuxtLink
          >
        </div>
      </Transition>
    </header>

    <!-- Page content -->
    <main class="marketing-layer">
      <slot />
    </main>

    <!-- ─── Footer ─── -->
    <footer class="marketing-dark-panel text-white marketing-layer border-t border-white/10">
      <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-12">
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
              Quote to invoice automation software for freelancers and small
              businesses — send quotes online, capture acceptance by email, and
              generate invoices automatically.
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
                  href="/#features"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Features</a
                >
              </li>
              <li>
                <a
                  href="/#pricing"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Pricing</a
                >
              </li>
              <li>
                <NuxtLink
                  to="/auth/register"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Get started</NuxtLink
                >
              </li>
              <li>
                <NuxtLink
                  to="/auth/login"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Sign in</NuxtLink
                >
              </li>
            </ul>
          </div>

          <!-- Guides -->
          <div>
            <h4
              class="text-xs font-semibold uppercase tracking-widest text-white/40 mb-4"
            >
              Guides
            </h4>
            <ul class="space-y-2.5">
              <li>
                <NuxtLink
                  to="/quote-to-invoice-software"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Quote to invoice software</NuxtLink
                >
              </li>
              <li>
                <NuxtLink
                  to="/invoice-automation"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Invoice automation</NuxtLink
                >
              </li>
              <li>
                <NuxtLink
                  to="/freelance-invoicing-tool"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Freelance invoicing tool</NuxtLink
                >
              </li>
              <li>
                <NuxtLink
                  to="/send-quotes-online"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Send quotes online</NuxtLink
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
                  href="mailto:support@easy-rp.com"
                  class="text-sm text-white/60 hover:text-white transition"
                  >support@easy-rp.com</a
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
                <NuxtLink
                  to="/auth/register"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Terms of Service</NuxtLink
                >
              </li>
              <li>
                <NuxtLink
                  to="/auth/register"
                  class="text-sm text-white/60 hover:text-white transition"
                  >Privacy Policy</NuxtLink
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
          <p class="text-xs text-white/50">
            Built for freelancers and growing teams across Europe.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
const mobileOpen = ref(false);
const scrolled = ref(false);

const onScroll = () => {
  scrolled.value = window.scrollY > 12;
};

onMounted(() => {
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();
});

onUnmounted(() => {
  window.removeEventListener("scroll", onScroll);
});
</script>
