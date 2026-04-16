<template>
  <NuxtLayout name="default" page-title="New Invoice">
    <UiAppCard title="Create Invoice">
      <InvoicesInvoiceForm
        :customers="customers"
        :products="products"
        :saving="saving"
        submit-label="Create Invoice"
        :errors="errors"
        @submit="handleSubmit"
        @cancel="navigateTo('/invoices')"
      />
    </UiAppCard>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { InvoiceFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "invoices",
});

const { createInvoice } = useInvoices();
const { customers, fetchCustomers } = useCustomers();
const { products, fetchProducts } = useProducts();
const toast = useToast();
const saving = ref(false);
const errors = ref<Record<string, string>>({});

await Promise.all([
  fetchCustomers({ per_page: 100 }),
  fetchProducts({ per_page: 100 }),
]);

async function handleSubmit(data: InvoiceFormData) {
  saving.value = true;
  errors.value = {};
  try {
    const invoice = await createInvoice(data);
    toast.success("Invoice created successfully.");
    navigateTo(`/invoices/${invoice.id}`);
  } catch (e: any) {
    if (e?.data?.errors) errors.value = e.data.errors;
    toast.apiError(e, "Failed to create invoice.");
  } finally {
    saving.value = false;
  }
}
</script>
