<template>
  <div v-if="product" class="space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between">
      <div class="flex items-center gap-4">
        <div
          class="w-16 h-16 rounded-2xl bg-surface-alt border border-border flex items-center justify-center overflow-hidden"
        >
          <Package class="w-8 h-8 text-text-muted" />
        </div>
        <div>
          <h2 class="text-xl font-bold text-text">{{ product.name }}</h2>
          <p class="text-sm text-text-muted">SKU: {{ product.sku }}</p>
        </div>
      </div>
      <div class="flex items-center gap-1.5">
        <UiAppBadge :variant="product.is_active ? 'success' : 'neutral'">
          {{ product.is_active ? "Active" : "Inactive" }}
        </UiAppBadge>
        <UiAppButton variant="ghost" size="xs" @click="$emit('edit')">
          <Pencil class="w-3.5 h-3.5" /> Edit
        </UiAppButton>
        <UiAppButton
          v-if="!hasLinkedRecords"
          variant="ghost"
          size="xs"
          class="text-danger! hover:bg-danger/10!"
          @click="$emit('delete')"
        >
          <Trash2 class="w-3.5 h-3.5" /> Delete
        </UiAppButton>
        <span
          v-else
          class="text-xs text-text-muted italic"
          title="Products used in orders or invoices cannot be deleted. Deactivate instead."
          >Cannot delete — in use</span
        >
      </div>
    </div>

    <!-- Detail Cards -->
    <div class="grid sm:grid-cols-3 gap-4">
      <UiAppStatCard label="Price" :value="formatCurrency(product.price)" />
      <UiAppStatCard
        label="Cost Price"
        :value="formatCurrency(product.cost_price)"
      />
      <UiAppStatCard
        label="Stock"
        :value="product.stock_quantity"
        :change="
          product.stock_quantity <= product.low_stock_threshold
            ? 'Low'
            : undefined
        "
        :trending="product.stock_quantity > product.low_stock_threshold"
      />
    </div>

    <!-- Info Grid -->
    <UiAppCard title="Details">
      <dl class="grid sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
        <div>
          <dt class="text-text-muted">Type</dt>
          <dd class="font-medium text-text capitalize">{{ product.type }}</dd>
        </div>
        <div>
          <dt class="text-text-muted">Tax Rate</dt>
          <dd class="font-medium text-text">{{ product.tax_rate }}%</dd>
        </div>
        <div>
          <dt class="text-text-muted">Categories</dt>
          <dd class="font-medium text-text">
            {{ product.categories?.map((c) => c.name).join(", ") || "—" }}
          </dd>
        </div>
        <div>
          <dt class="text-text-muted">Track Inventory</dt>
          <dd class="font-medium text-text">
            {{ product.track_inventory ? "Yes" : "No" }}
          </dd>
        </div>
        <div class="sm:col-span-2">
          <dt class="text-text-muted">Description</dt>
          <dd class="font-medium text-text">
            {{ product.description || "—" }}
          </dd>
        </div>
      </dl>
    </UiAppCard>
  </div>
</template>

<script setup lang="ts">
import type { Product } from "~/types";
import { Package, Pencil, Trash2 } from "lucide-vue-next";

const props = withDefaults(
  defineProps<{
    product: Product | null;
    hasLinkedRecords?: boolean;
  }>(),
  {
    hasLinkedRecords: false,
  },
);

defineEmits<{
  edit: [];
  delete: [];
}>();

const { formatCurrency } = useCurrency();
</script>
