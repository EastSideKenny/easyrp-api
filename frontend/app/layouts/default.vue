<template>
  <div class="min-h-screen bg-surface-alt font-sans text-text">
    <LayoutAppSidebar
      :open="sidebarOpen"
      :user-name="user?.name"
      :user-email="user?.email"
      @close="sidebarOpen = false"
    />

    <div class="lg:pl-64 flex flex-col min-h-screen">
      <LayoutAppTopbar
        :title="pageTitle"
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
      >
        <template #actions>
          <slot name="actions" />
        </template>
      </LayoutAppTopbar>

      <LayoutTrialBanner />

      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    pageTitle?: string;
  }>(),
  {
    pageTitle: "Dashboard",
  },
);

const sidebarOpen = ref(false);
const { user } = useAuth();

const pageTitle = computed(() => props.pageTitle);
</script>
