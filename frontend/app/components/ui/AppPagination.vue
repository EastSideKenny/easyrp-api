<template>
  <nav class="flex items-center justify-end">
    <div v-if="totalPages > 1" class="flex items-center gap-1">
      <button
        :disabled="currentPage <= 1"
        class="p-1.5 rounded-lg border border-border hover:bg-surface-alt disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        @click="$emit('update:currentPage', currentPage - 1)"
      >
        <ChevronLeft class="w-4 h-4" />
      </button>
      <button
        v-for="page in visiblePages"
        :key="page"
        class="px-3 py-1.5 text-sm rounded-lg transition-colors font-medium"
        :class="
          page === currentPage
            ? 'bg-primary text-white'
            : 'hover:bg-surface-alt text-text-secondary'
        "
        @click="$emit('update:currentPage', page)"
      >
        {{ page }}
      </button>
      <button
        :disabled="currentPage >= totalPages"
        class="p-1.5 rounded-lg border border-border hover:bg-surface-alt disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        @click="$emit('update:currentPage', currentPage + 1)"
      >
        <ChevronRight class="w-4 h-4" />
      </button>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ChevronLeft, ChevronRight } from "lucide-vue-next";

const props = defineProps<{
  currentPage: number;
  totalPages: number;
  total: number;
  from: number;
  to: number;
}>();

defineEmits<{
  "update:currentPage": [page: number];
}>();

const visiblePages = computed(() => {
  const pages: number[] = [];
  const start = Math.max(1, props.currentPage - 2);
  const end = Math.min(props.totalPages, props.currentPage + 2);
  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});
</script>
