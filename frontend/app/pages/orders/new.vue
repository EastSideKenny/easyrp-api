<template>
  <NuxtLayout name="default" page-title="New Order">
    <UiAppCard title="Create Order">
      <div
        v-if="pageLoading"
        class="flex items-center justify-center py-16 text-text-muted"
      >
        <svg class="w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
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
      <OrdersOrderForm
        v-else
        :customers="customers"
        :products="products"
        :saving="saving"
        submit-label="Create Order"
        :errors="errors"
        @submit="handleSubmit"
        @cancel="navigateTo('/orders')"
      />
    </UiAppCard>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { OrderFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth"],
});

const { createOrder } = useOrders();
const { customers, fetchCustomers } = useCustomers();
const { products, fetchProducts } = useProducts();
const toast = useToast();
const saving = ref(false);
const errors = ref<Record<string, string>>({});
const pageLoading = ref(true);

onMounted(async () => {
  await Promise.all([
    fetchCustomers({ per_page: 100 }),
    fetchProducts({ per_page: 100 }),
  ]);
  pageLoading.value = false;
});

async function handleSubmit(
  data: OrderFormData & { subtotal: number; tax_total: number; total: number },
) {
  saving.value = true;
  errors.value = {};
  try {
    const order = await createOrder(data);
    toast.success("Order created successfully.");
    navigateTo(`/orders`);
  } catch (e: any) {
    if (e?.response?._data?.errors) {
      const fieldErrors: Record<string, string> = {};
      for (const [key, messages] of Object.entries(e.response._data.errors)) {
        fieldErrors[key] = (messages as string[])[0] ?? "Invalid";
      }
      errors.value = fieldErrors;
    }
    toast.apiError(e, "Failed to create order.");
  } finally {
    saving.value = false;
  }
}
</script>
