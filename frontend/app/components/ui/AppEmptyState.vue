<template>
  <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
    <div
      class="w-14 h-14 rounded-2xl bg-surface-alt border border-border flex items-center justify-center mb-5"
    >
      <slot name="icon">
        <component :is="resolvedIcon" class="w-6 h-6 text-text-muted" />
      </slot>
    </div>
    <h3 class="text-base font-semibold text-text mb-1">{{ title }}</h3>
    <p class="text-sm text-text-secondary max-w-sm mb-6">{{ description }}</p>
    <slot name="action" />
  </div>
</template>

<script setup lang="ts">
import type { Component } from "vue";
import { Inbox } from "lucide-vue-next";

const props = defineProps<{
  icon?: Component;
  title: string;
  description: string;
}>();

const resolvedIcon = computed(() => props.icon ?? Inbox);
</script>
