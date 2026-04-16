<template>
  <header
    class="sticky top-0 z-20 bg-white/80 backdrop-blur-xl border-b border-border h-14"
  >
    <div class="h-full px-5 flex items-center justify-between gap-4">
      <!-- Mobile menu button -->
      <button
        class="lg:hidden text-text-secondary hover:text-text p-1.5 -ml-1.5 rounded-lg hover:bg-surface-alt transition-colors"
        @click="$emit('toggle-sidebar')"
      >
        <Menu class="w-5 h-5" />
      </button>

      <!-- Page title -->
      <div class="flex-1 min-w-0">
        <h1 class="text-sm font-semibold text-text truncate">{{ title }}</h1>
      </div>

      <!-- Right actions -->
      <div class="flex items-center gap-1">
        <slot name="actions" />
        <div class="w-px h-5 bg-border mx-1" />
        <button
          class="p-2 rounded-lg text-text-muted hover:text-text hover:bg-surface-alt transition-colors"
          title="Notifications"
        >
          <Bell class="w-4 h-4" />
        </button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { Menu, Bell } from "lucide-vue-next";

defineProps<{
  title: string;
}>();

defineEmits<{
  "toggle-sidebar": [];
}>();
</script>
