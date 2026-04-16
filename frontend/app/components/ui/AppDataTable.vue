<template>
  <div
    class="bg-surface border border-border rounded-2xl overflow-hidden shadow-card"
  >
    <!-- Toolbar -->
    <div
      v-if="$slots.toolbar"
      class="px-6 py-4 border-b border-border flex items-center justify-between gap-4"
    >
      <slot name="toolbar" />
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-border bg-surface-alt">
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider whitespace-nowrap"
              :class="[
                col.align === 'right'
                  ? 'text-right'
                  : col.align === 'center'
                    ? 'text-center'
                    : 'text-left',
                col.sortable
                  ? 'cursor-pointer select-none hover:text-text transition-colors'
                  : '',
                col.class,
              ]"
              @click="col.sortable && $emit('sort', col.key)"
            >
              <span class="inline-flex items-center gap-1">
                {{ col.label }}
                <svg
                  v-if="col.sortable && sortBy === col.key"
                  class="w-3.5 h-3.5 transition-transform"
                  :class="sortDir === 'desc' ? 'rotate-180' : ''"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 15l7-7 7 7"
                  />
                </svg>
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td :colspan="columns.length" class="px-6 py-12 text-center">
              <div
                class="flex items-center justify-center gap-2 text-text-muted"
              >
                <svg
                  class="w-5 h-5 animate-spin"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                  />
                </svg>
                Loading…
              </div>
            </td>
          </tr>
          <tr v-else-if="!rows.length">
            <td
              :colspan="columns.length"
              class="px-6 py-12 text-center text-text-muted"
            >
              <slot name="empty">No data found.</slot>
            </td>
          </tr>
          <template v-else>
            <tr
              v-for="(row, idx) in rows"
              :key="idx"
              class="border-b border-border-light last:border-0 hover:bg-surface-alt/60 transition-colors duration-150"
              :class="clickable ? 'cursor-pointer' : ''"
              @click="$emit('row-click', row)"
            >
              <slot name="row" :row="row" :index="idx" />
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="$slots.pagination" class="px-6 py-4 border-t border-border">
      <slot name="pagination" />
    </div>
  </div>
</template>

<script setup lang="ts" generic="T">
import type { TableColumn } from "~/types";

withDefaults(
  defineProps<{
    columns: TableColumn[];
    rows: T[];
    loading?: boolean;
    sortBy?: string;
    sortDir?: "asc" | "desc";
    clickable?: boolean;
  }>(),
  {
    rows: () => [] as T[],
    loading: false,
    sortDir: "asc",
    clickable: false,
  },
);

defineEmits<{
  sort: [key: string];
  "row-click": [row: T];
}>();
</script>
