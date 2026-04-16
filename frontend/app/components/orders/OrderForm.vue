<template>
  <form class="space-y-6" @submit.prevent="handleSubmit">
    <div class="grid sm:grid-cols-2 gap-5">
      <UiAppFormField label="Customer" :error="errors.customer_id" required>
        <select
          v-model.number="form.customer_id"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
        >
          <option :value="null" disabled>Select customer…</option>
          <option v-for="c in customers" :key="c.id" :value="c.id">
            {{ c.name }}
          </option>
        </select>
      </UiAppFormField>

      <UiAppFormField label="Status">
        <select
          v-model="form.status"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
        >
          <option value="pending">Pending</option>
          <option value="paid">Paid</option>
          <option value="done">Done</option>
          <option value="canceled">Canceled</option>
        </select>
      </UiAppFormField>
    </div>

    <!-- Line Items -->
    <UiAppCard title="Items" :no-padding="false">
      <OrdersOrderLineItems
        ref="lineItemsRef"
        v-model="form.items"
        :products="products ?? []"
      />
    </UiAppCard>

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
import type {
  Customer,
  Product,
  OrderFormData,
  OrderItemFormData,
} from "~/types";

const props = withDefaults(
  defineProps<{
    customers: Customer[];
    products?: Product[];
    initialData?: Partial<OrderFormData>;
    saving?: boolean;
    submitLabel?: string;
    errors?: Record<string, string>;
  }>(),
  {
    saving: false,
    submitLabel: "Create Order",
    errors: () => ({}),
  },
);

const emit = defineEmits<{
  submit: [
    data: OrderFormData & {
      subtotal: number;
      tax_total: number;
      total: number;
    },
  ];
  cancel: [];
}>();

const lineItemsRef = ref<{
  subtotal: number;
  taxTotal: number;
  total: number;
}>();

const form = reactive<OrderFormData>({
  customer_id: props.initialData?.customer_id ?? null,
  status: props.initialData?.status ?? "pending",
  items: props.initialData?.items ?? [],
});

function handleSubmit() {
  emit("submit", {
    ...form,
    subtotal: lineItemsRef.value?.subtotal ?? 0,
    tax_total: lineItemsRef.value?.taxTotal ?? 0,
    total: lineItemsRef.value?.total ?? 0,
  });
}
</script>
