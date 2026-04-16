<template>
  <div
    class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden"
  >
    <div
      v-if="title || $slots.header"
      class="px-6 py-4 border-b border-border flex items-center justify-between"
    >
      <h3 v-if="title" class="text-base font-semibold text-text">
        {{ title }}
      </h3>
      <slot name="header" />
    </div>
    <div :class="noPadding ? '' : 'p-6'">
      <slot />
    </div>
    <div
      v-if="$slots.footer"
      class="px-6 py-4 border-t border-border bg-surface-alt"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    title?: string;
    noPadding?: boolean;
  }>(),
  {
    noPadding: false,
  },
);
</script>
