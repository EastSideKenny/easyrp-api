<template>
  <div>
    <!-- Filters -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8"
    >
      <h2 class="text-2xl font-bold text-text">All Products</h2>
      <div class="flex items-center gap-3">
        <UiAppSearchInput
          v-model="search"
          placeholder="Search products…"
          class="w-64"
        />
        <select
          v-model="productType"
          class="border border-border rounded-xl px-3 py-2.5 text-sm bg-surface-alt focus:outline-none focus:border-primary transition-all"
        >
          <option value="">All Types</option>
          <option value="physical">Physical</option>
          <option value="service">Service</option>
        </select>
      </div>
    </div>

    <!-- Product Grid -->
    <div
      v-if="loading"
      class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5"
    >
      <div
        v-for="i in 8"
        :key="i"
        class="bg-surface border border-border rounded-2xl overflow-hidden animate-pulse"
      >
        <div class="aspect-square bg-surface-alt" />
        <div class="p-4 space-y-3">
          <div class="h-3 bg-surface-alt rounded w-1/3" />
          <div class="h-4 bg-surface-alt rounded w-3/4" />
          <div class="h-5 bg-surface-alt rounded w-1/4" />
          <div class="h-10 bg-surface-alt rounded" />
        </div>
      </div>
    </div>

    <div
      v-else-if="products.length"
      class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5"
    >
      <StoreProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
        @add-to-cart="$emit('add-to-cart', $event)"
      />
    </div>

    <UiAppEmptyState
      v-else
      title="No products found"
      description="Try adjusting your search or filter criteria."
    >
      <template #icon>
        <span class="text-2xl leading-none">🔍</span>
      </template>
    </UiAppEmptyState>
  </div>
</template>

<script setup lang="ts">
import type { Product } from "~/types";

defineProps<{
  products: Product[];
  loading: boolean;
}>();

defineEmits<{
  "add-to-cart": [product: Product];
}>();

const search = defineModel<string>("search", { default: "" });
const productType = defineModel<string>("productType", { default: "" });
</script>
