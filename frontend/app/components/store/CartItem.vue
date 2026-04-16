<template>
  <div
    class="flex items-center gap-4 py-4 border-b border-border-light last:border-0"
  >
    <!-- Image -->
    <div
      class="w-16 h-16 rounded-xl bg-surface-alt border border-border flex items-center justify-center shrink-0 overflow-hidden"
    >
      <Package class="w-7 h-7 text-text-muted" />
    </div>

    <!-- Details -->
    <div class="flex-1 min-w-0">
      <p class="text-sm font-semibold text-text truncate">
        {{ item.product.name }}
      </p>
      <p class="text-xs text-text-muted">
        {{ formatCurrency(item.product.price) }} each
      </p>
    </div>

    <!-- Quantity -->
    <div class="flex items-center gap-1">
      <button
        class="w-7 h-7 rounded-lg border border-border flex items-center justify-center text-sm hover:bg-surface-alt transition-colors"
        @click="$emit('update-qty', item.product.id, item.quantity - 1)"
      >
        −
      </button>
      <span class="w-8 text-center text-sm font-medium tabular-nums">{{
        item.quantity
      }}</span>
      <button
        class="w-7 h-7 rounded-lg border border-border flex items-center justify-center text-sm hover:bg-surface-alt transition-colors"
        @click="$emit('update-qty', item.product.id, item.quantity + 1)"
      >
        +
      </button>
    </div>

    <!-- Line total -->
    <p class="text-sm font-semibold text-text tabular-nums w-20 text-right">
      {{ formatCurrency(item.product.price * item.quantity) }}
    </p>

    <!-- Remove -->
    <button
      class="text-text-muted hover:text-danger transition-colors p-1"
      @click="$emit('remove', item.product.id)"
    >
      <svg
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M6 18L18 6M6 6l12 12"
        />
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
import type { CartItem } from "~/types";
import { Package } from "lucide-vue-next";

defineProps<{
  item: CartItem;
}>();

defineEmits<{
  "update-qty": [productId: number, quantity: number];
  remove: [productId: number];
}>();

const { formatCurrency } = useCurrency();
</script>
