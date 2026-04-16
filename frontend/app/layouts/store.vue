<template>
  <div ref="storeRoot" class="min-h-screen bg-surface-alt font-sans text-text">
    <!-- Simple store header -->
    <header
      class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-border"
    >
      <div
        class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between"
      >
        <NuxtLink to="/store" class="flex items-center gap-2.5">
          <div
            class="w-8 h-8 rounded-xl bg-linear-to-br from-primary to-primary-light flex items-center justify-center"
          >
            <span class="text-white text-sm font-bold">{{
              storeName.charAt(0)
            }}</span>
          </div>
          <span class="font-semibold text-base tracking-tight text-text">{{
            storeName
          }}</span>
        </NuxtLink>
        <div class="flex items-center gap-3">
          <slot name="header-actions" />
        </div>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8">
      <slot />
    </main>

    <footer class="border-t border-border mt-12">
      <div
        class="max-w-6xl mx-auto px-6 py-6 text-center text-xs text-text-muted"
      >
        © 2026 {{ storeName }}. All rights reserved.
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
const { storeName, loadSettings, applyTheme, primaryColor } = useStoreSettings();

const storeRoot = ref<HTMLElement | null>(null);

onMounted(async () => {
  await loadSettings();
  applyTheme(storeRoot.value);
});

// Re-apply theme if settings change (e.g. admin saves new colour in another tab)
watch(
  () => primaryColor.value,
  () => applyTheme(storeRoot.value),
);
</script>
