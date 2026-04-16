<template>
  <div
    class="group bg-surface border border-border rounded-2xl overflow-hidden hover:shadow-elevated hover:border-primary/20 transition-all duration-300"
  >
    <!-- Image -->
    <div
      class="aspect-square bg-surface-alt flex items-center justify-center overflow-hidden"
    >
      <Package class="w-14 h-14 text-text-muted/40" />
    </div>

    <!-- Info -->
    <div class="p-4 space-y-2">
      <p class="text-xs text-text-muted uppercase tracking-wider">
        {{ product.type }}
      </p>
      <h3 class="text-sm font-semibold text-text line-clamp-2 leading-snug">
        {{ product.name }}
      </h3>
      <div class="flex items-center justify-between pt-1">
        <p class="text-lg font-bold text-text tabular-nums">
          {{ formatCurrency(product.price, currency) }}
        </p>
        <UiAppBadge v-if="isOutOfStock" variant="danger" :dot="false">
          Sold out
        </UiAppBadge>
      </div>
      <button
        :disabled="isOutOfStock"
        class="w-full mt-2 bg-primary text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-primary-dark hover:shadow-elevated focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        @click="$emit('add-to-cart', product)"
      >
        {{ isOutOfStock ? "Out of Stock" : "Add to Cart" }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Product } from "~/types";
import { Package } from "lucide-vue-next";

const props = defineProps<{
  product: Product;
}>();

defineEmits<{
  "add-to-cart": [product: Product];
}>();

const { formatCurrency } = useCurrency();
const { currency } = useStoreSettings();

const isOutOfStock = computed(
  () => props.product.type === "physical" && props.product.stock_quantity <= 0,
);
</script>
