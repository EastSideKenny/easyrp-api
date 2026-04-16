<template>
  <form class="space-y-6" @submit.prevent="$emit('submit', form)">
    <div class="grid sm:grid-cols-2 gap-5">
      <UiAppFormField label="Product Name" :error="errors.name" required>
        <input
          v-model="form.name"
          type="text"
          placeholder="e.g. Wireless Keyboard"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
        />
      </UiAppFormField>

      <UiAppFormField label="SKU" :error="errors.sku" required>
        <input
          v-model="form.sku"
          type="text"
          placeholder="e.g. KB-001"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
        />
      </UiAppFormField>

      <UiAppFormField label="Price" :error="errors.price" required>
        <div class="relative">
          <span
            class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-text-muted"
            >{{ currencySymbol }}</span
          >
          <input
            v-model.number="form.price"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            class="w-full border border-border rounded-xl pl-8 pr-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
          />
        </div>
      </UiAppFormField>

      <UiAppFormField label="Cost Price" :error="errors.cost_price">
        <div class="relative">
          <span
            class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-text-muted"
            >{{ currencySymbol }}</span
          >
          <input
            v-model.number="form.cost_price"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            class="w-full border border-border rounded-xl pl-8 pr-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
          />
        </div>
      </UiAppFormField>

      <UiAppFormField label="Type" required>
        <select
          v-model="form.type"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200 appearance-none"
        >
          <option value="physical">Physical</option>
          <option value="service">Service</option>
        </select>
      </UiAppFormField>

      <UiAppFormField label="Tax Rate (%)">
        <input
          v-model.number="form.tax_rate"
          type="number"
          step="0.01"
          min="0"
          placeholder="0"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
        />
      </UiAppFormField>

      <UiAppFormField label="Stock Quantity">
        <input
          v-model.number="form.stock_quantity"
          type="number"
          min="0"
          placeholder="0"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
        />
      </UiAppFormField>

      <UiAppFormField label="Low Stock Threshold">
        <input
          v-model.number="form.low_stock_threshold"
          type="number"
          min="0"
          placeholder="5"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
        />
      </UiAppFormField>
    </div>

    <UiAppFormField label="Description">
      <textarea
        v-model="form.description"
        rows="3"
        placeholder="Describe your product…"
        class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200 resize-none"
      />
    </UiAppFormField>

    <div class="flex items-center gap-6">
      <label class="flex items-center gap-2 cursor-pointer">
        <input
          v-model="form.is_active"
          type="checkbox"
          class="w-4 h-4 rounded border-border text-primary focus:ring-primary/20"
        />
        <span class="text-sm text-text">Active (visible in store)</span>
      </label>
      <label class="flex items-center gap-2 cursor-pointer">
        <input
          v-model="form.track_inventory"
          type="checkbox"
          class="w-4 h-4 rounded border-border text-primary focus:ring-primary/20"
        />
        <span class="text-sm text-text">Track inventory</span>
      </label>
    </div>

    <div
      class="flex items-center justify-end gap-3 pt-4 border-t border-border"
    >
      <UiAppButton variant="outline" @click="$emit('cancel')"
        >Cancel</UiAppButton
      >
      <UiAppButton type="submit" :loading="saving">{{
        submitLabel
      }}</UiAppButton>
    </div>
  </form>
</template>

<script setup lang="ts">
import type { ProductFormData } from "~/types";

const props = withDefaults(
  defineProps<{
    initialData?: Partial<ProductFormData>;
    saving?: boolean;
    submitLabel?: string;
    errors?: Record<string, string>;
  }>(),
  {
    saving: false,
    submitLabel: "Save Product",
    errors: () => ({}),
  },
);

defineEmits<{
  submit: [data: ProductFormData];
  cancel: [];
}>();

const { tenantCurrency } = useCurrency();
const currencySymbol = computed(() =>
  (0)
    .toLocaleString("en", {
      style: "currency",
      currency: tenantCurrency.value,
      minimumFractionDigits: 0,
    })
    .replace(/[\d.,\s]/g, "")
    .trim(),
);

const form = reactive<ProductFormData>({
  name: props.initialData?.name ?? "",
  sku: props.initialData?.sku ?? "",
  description: props.initialData?.description ?? "",
  type: props.initialData?.type ?? "physical",
  price: props.initialData?.price ?? 0,
  cost_price: props.initialData?.cost_price ?? 0,
  tax_rate: props.initialData?.tax_rate ?? 0,
  is_active: props.initialData?.is_active ?? true,
  track_inventory: props.initialData?.track_inventory ?? true,
  stock_quantity: props.initialData?.stock_quantity ?? 0,
  low_stock_threshold: props.initialData?.low_stock_threshold ?? 5,
  category_ids: props.initialData?.category_ids ?? [],
});
</script>
