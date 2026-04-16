<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-semibold text-text">Line Items</h3>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="text-sm text-primary font-medium hover:text-primary-dark transition-colors"
          @click="addProductLine"
        >
          + Add Product
        </button>
        <span class="text-border">|</span>
        <button
          type="button"
          class="text-sm text-text-muted font-medium hover:text-text transition-colors"
          @click="addCustomLine"
        >
          + Custom Item
        </button>
      </div>
    </div>

    <!-- Table header -->
    <div
      class="hidden sm:grid grid-cols-12 gap-3 px-1 text-xs font-semibold text-text-muted uppercase tracking-wider"
    >
      <div class="col-span-4">Product / Description</div>
      <div class="col-span-2">Qty</div>
      <div class="col-span-2">Unit Price</div>
      <div class="col-span-1">Tax %</div>
      <div class="col-span-2 text-right">Total</div>
      <div class="col-span-1" />
    </div>

    <!-- Line items -->
    <div
      v-for="(line, idx) in modelValue"
      :key="idx"
      class="grid grid-cols-12 gap-3 items-start bg-surface-alt border border-border rounded-xl p-3"
    >
      <!-- Product / Description column -->
      <div class="col-span-12 sm:col-span-4 space-y-2">
        <!-- Product selector (shown when line is a product line) -->
        <div v-if="lineIsProduct(idx)">
          <select
            :value="line.product_id"
            class="w-full border border-border rounded-lg px-3 py-2 text-sm bg-surface focus:outline-none focus:border-primary transition-all appearance-none"
            @change="
              onProductSelect(idx, ($event.target as HTMLSelectElement).value)
            "
          >
            <option :value="''" disabled>Select product…</option>
            <option v-for="p in products" :key="p.id" :value="p.id">
              {{ p.name }} ({{ p.sku }})
            </option>
          </select>
          <input
            v-model="line.description"
            type="text"
            placeholder="Additional notes (optional)"
            class="mt-2 w-full border border-border rounded-lg px-3 py-1.5 text-xs bg-surface focus:outline-none focus:border-primary transition-all text-text-muted"
          />
        </div>
        <!-- Free-text description -->
        <div v-else>
          <input
            v-model="line.description"
            type="text"
            placeholder="Item description"
            class="w-full border border-border rounded-lg px-3 py-2 text-sm bg-surface focus:outline-none focus:border-primary transition-all"
          />
        </div>
      </div>

      <div class="col-span-4 sm:col-span-2">
        <input
          v-model.number="line.quantity"
          type="number"
          min="1"
          class="w-full border border-border rounded-lg px-3 py-2 text-sm bg-surface focus:outline-none focus:border-primary transition-all tabular-nums"
        />
      </div>
      <div class="col-span-4 sm:col-span-2">
        <input
          v-model.number="line.unit_price"
          type="number"
          step="0.01"
          min="0"
          class="w-full border border-border rounded-lg px-3 py-2 text-sm bg-surface focus:outline-none focus:border-primary transition-all tabular-nums"
        />
      </div>
      <div class="col-span-3 sm:col-span-1">
        <input
          v-model.number="line.tax_rate"
          type="number"
          step="0.01"
          min="0"
          class="w-full border border-border rounded-lg px-3 py-2 text-sm bg-surface focus:outline-none focus:border-primary transition-all tabular-nums"
        />
      </div>
      <div class="col-span-4 sm:col-span-2 flex items-center justify-end">
        <span class="text-sm font-medium text-text tabular-nums">
          {{ formatCurrency(line.line_total) }}
        </span>
      </div>
      <div class="col-span-1 flex items-center justify-center">
        <button
          type="button"
          class="text-text-muted hover:text-danger transition-colors p-1"
          @click="removeLine(idx)"
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
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            />
          </svg>
        </button>
      </div>
    </div>

    <!-- Empty state -->
    <div
      v-if="!modelValue.length"
      class="text-center py-8 text-sm text-text-muted border border-dashed border-border rounded-xl"
    >
      No items added. Click "+ Add Product" or "+ Custom Item" to begin.
    </div>

    <!-- Totals -->
    <div class="flex justify-end">
      <div class="w-64 space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-text-muted">Subtotal</span>
          <span class="font-medium text-text tabular-nums">{{
            formatCurrency(subtotal)
          }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-text-muted">Tax</span>
          <span class="font-medium text-text tabular-nums">{{
            formatCurrency(taxTotal)
          }}</span>
        </div>
        <div class="flex justify-between pt-2 border-t border-border">
          <span class="font-semibold text-text">Total</span>
          <span class="font-bold text-text text-base tabular-nums">{{
            formatCurrency(total)
          }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { OrderItemFormData, Product } from "~/types";

const props = defineProps<{
  products: Product[];
}>();

const modelValue = defineModel<OrderItemFormData[]>({ required: true });

const { formatCurrency } = useCurrency();

// Keep line_total in sync
watch(
  modelValue,
  (items) => {
    for (const line of items) {
      line.line_total =
        line.quantity * line.unit_price * (1 + line.tax_rate / 100);
    }
  },
  { deep: true },
);

const subtotal = computed(() =>
  modelValue.value.reduce((sum, l) => sum + l.quantity * l.unit_price, 0),
);

const taxTotal = computed(() =>
  modelValue.value.reduce(
    (sum, l) => sum + l.quantity * l.unit_price * (l.tax_rate / 100),
    0,
  ),
);

const total = computed(() => subtotal.value + taxTotal.value);

defineExpose({ subtotal, taxTotal, total });

// Track which lines are product-based vs custom
const productLines = ref<Set<number>>(new Set());

// Initialize product lines from existing items (for edit mode)
watch(
  modelValue,
  (items) => {
    if (productLines.value.size === 0 && items.length > 0) {
      const initial = new Set<number>();
      items.forEach((item, idx) => {
        if (item.product_id != null) initial.add(idx);
      });
      if (initial.size > 0) productLines.value = initial;
    }
  },
  { immediate: true },
);

function lineIsProduct(idx: number): boolean {
  return productLines.value.has(idx);
}

function addProductLine() {
  const idx = modelValue.value.length;
  modelValue.value.push({
    product_id: null,
    description: "",
    quantity: 1,
    unit_price: 0,
    tax_rate: 0,
    line_total: 0,
  });
  productLines.value = new Set([...productLines.value, idx]);
}

function addCustomLine() {
  modelValue.value.push({
    product_id: null,
    description: "",
    quantity: 1,
    unit_price: 0,
    tax_rate: 0,
    line_total: 0,
  });
}

function onProductSelect(idx: number, value: string) {
  const productId = Number(value);
  const product = props.products.find((p) => p.id === productId);
  if (!product) return;

  const line = modelValue.value[idx];
  if (!line) return;

  line.product_id = product.id;
  line.description = product.name;
  line.unit_price = Number(product.price);
  line.tax_rate = Number(product.tax_rate);
}

function removeLine(idx: number) {
  modelValue.value.splice(idx, 1);
  // Rebuild the product-lines set with shifted indices
  const newSet = new Set<number>();
  for (const i of productLines.value) {
    if (i < idx) newSet.add(i);
    else if (i > idx) newSet.add(i - 1);
    // i === idx is removed
  }
  productLines.value = newSet;
}
</script>
