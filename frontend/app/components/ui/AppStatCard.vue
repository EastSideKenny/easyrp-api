<template>
  <div
    class="group bg-surface border border-border rounded-xl p-5 space-y-3 hover:shadow-elevated transition-all duration-300"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div
          v-if="icon"
          class="w-8 h-8 rounded-lg flex items-center justify-center"
          :class="iconBg"
        >
          <component :is="icon" class="w-4 h-4" :class="iconColor" />
        </div>
        <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">
          {{ label }}
        </p>
      </div>
      <span
        v-if="change !== undefined"
        class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md"
        :class="
          trending ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger'
        "
      >
        <TrendingUp v-if="trending" class="w-3 h-3" />
        <TrendingDown v-else class="w-3 h-3" />
        {{ change }}
      </span>
    </div>
    <p class="text-3xl font-bold text-text tracking-tight tabular-nums">
      {{ value }}
    </p>
    <p v-if="subtitle" class="text-xs text-text-muted">{{ subtitle }}</p>
  </div>
</template>

<script setup lang="ts">
import type { Component } from 'vue';
import { TrendingUp, TrendingDown } from 'lucide-vue-next';

withDefaults(
  defineProps<{
    label: string;
    value: string | number;
    change?: string;
    trending?: boolean;
    subtitle?: string;
    icon?: Component;
    iconBg?: string;
    iconColor?: string;
  }>(),
  {
    trending: true,
    iconBg: 'bg-primary/10',
    iconColor: 'text-primary',
  },
);
</script>
