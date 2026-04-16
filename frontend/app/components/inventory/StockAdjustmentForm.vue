<template>
  <form class="space-y-6" @submit.prevent="handleSubmit">
    <div class="grid sm:grid-cols-2 gap-5">
      <UiAppFormField label="Product" :error="errors.product_id" required>
        <select
          v-model.number="form.product_id"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
        >
          <option :value="0" disabled>Select product…</option>
          <option v-for="p in products" :key="p.id" :value="p.id">
            {{ p.name }} ({{ p.sku }}) — Current: {{ p.stock_quantity }}
          </option>
        </select>
      </UiAppFormField>

      <UiAppFormField label="Movement Type" required>
        <select
          v-model="form.type"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
        >
          <option value="manual_adjustment">Manual Adjustment</option>
          <option value="sale">Sale</option>
        </select>
      </UiAppFormField>

      <UiAppFormField
        :label="form.type === 'sale' ? 'Quantity Sold' : 'Quantity Change'"
        :error="errors.quantity_change"
        required
      >
        <input
          v-model.number="form.quantity_change"
          type="number"
          min="0"
          :placeholder="form.type === 'sale' ? 'e.g. 5' : 'e.g. 5 or -3'"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
        />
        <p v-if="form.type === 'sale'" class="text-xs text-text-muted mt-1">
          This will subtract {{ form.quantity_change || 0 }} units from stock.
        </p>
      </UiAppFormField>
    </div>

    <div
      class="flex items-center justify-end gap-3 pt-4 border-t border-border"
    >
      <UiAppButton variant="outline" @click="$emit('cancel')"
        >Cancel</UiAppButton
      >
      <UiAppButton type="submit" :loading="saving">Save Movement</UiAppButton>
    </div>
  </form>
</template>

<script setup lang="ts">
import type { Product, StockMovementFormData } from "~/types";

const props = withDefaults(
  defineProps<{
    products: Product[];
    preselectedProductId?: number;
    saving?: boolean;
    errors?: Record<string, string>;
  }>(),
  {
    saving: false,
    errors: () => ({}),
  },
);

const emit = defineEmits<{
  submit: [data: StockMovementFormData];
  cancel: [];
}>();

const form = reactive<StockMovementFormData>({
  product_id: props.preselectedProductId ?? 0,
  type: "manual_adjustment",
  quantity_change: 0,
});

function handleSubmit() {
  const data = { ...form };
  // For sales, ensure quantity is negative (user enters positive number)
  if (data.type === "sale" && data.quantity_change > 0) {
    data.quantity_change = -data.quantity_change;
  }
  emit("submit", data);
}
</script>
