<template>
  <div class="flex items-center gap-3">
    <h2 class="text-lg font-semibold text-text">{{ title }}</h2>
    <div class="flex-1 h-px bg-border" />
    <slot />
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
}>();
</script>
