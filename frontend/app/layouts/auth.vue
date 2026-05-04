<template>
  <div class="min-h-screen bg-surface-alt font-sans text-text flex">
    <!-- Left panel — branding / illustration -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-text">
      <!-- Decorative gradient blobs -->
      <div class="absolute inset-0">
        <div
          class="absolute -top-24 -left-24 w-96 h-96 bg-primary/30 rounded-full blur-3xl"
        />
        <div
          class="absolute bottom-0 right-0 w-80 h-80 bg-secondary/20 rounded-full blur-3xl"
        />
        <div
          class="absolute top-1/2 left-1/3 w-64 h-64 bg-primary-light/20 rounded-full blur-3xl"
        />
      </div>

      <!-- Grid pattern overlay -->
      <div
        class="absolute inset-0 opacity-[0.04]"
        style="
          background-image: radial-gradient(circle, #fff 1px, transparent 1px);
          background-size: 24px 24px;
        "
      />

      <!-- Content -->
      <div class="relative z-10 flex flex-col justify-between p-12 w-full">
        <NuxtLink to="/" class="flex items-center gap-2.5">
          <div
            class="w-9 h-9 rounded-xl bg-linear-to-br from-primary to-primary-light flex items-center justify-center"
          >
            <span class="text-white text-sm font-bold">E</span>
          </div>
          <span class="font-semibold text-lg tracking-tight text-white"
            >EasyRP</span
          >
        </NuxtLink>

        <div class="max-w-md">
          <p class="text-2xl font-semibold text-white/90 leading-relaxed">
            One platform for invoices, inventory, orders, and growth.
          </p>
        </div>
      </div>
    </div>

    <!-- Right panel — form area -->
    <div class="flex-1 flex flex-col">
      <!-- Mobile header -->
      <div class="lg:hidden px-6 py-4 border-b border-border">
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
      </div>

      <!-- Form content -->
      <div class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-sm">
          <slot />
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 text-center text-xs text-text-muted">
        © {{ new Date().getFullYear() }} EasyRP. All rights reserved.
      </div>
    </div>
  </div>
</template>
