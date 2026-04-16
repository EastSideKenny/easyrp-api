<template>
  <NuxtLayout name="default" page-title="Payments">
    <PaymentsPaymentList
      :payments="payments"
      :loading="loading"
      :meta="meta"
      v-model:search="search"
      @create="showModal = true"
      @view="(id: number) => navigateTo(`/payments/${id}`)"
      @page-change="(page: number) => (currentPage = page)"
    />

    <!-- Record Payment Modal -->
    <UiAppModal v-model="showModal" title="Record Payment" size="lg">
      <PaymentsPaymentForm
        :invoices="invoiceList"
        :customers="customerList"
        :products="productList"
        :saving="saving"
        :errors="errors"
        @submit="handleSubmit"
        @cancel="showModal = false"
        @invoice-created="handleInvoiceCreated"
      />
    </UiAppModal>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { PaymentFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "invoices",
});

const { payments, loading, meta, fetchPayments, createPayment } = usePayments();
const { invoices: invoiceList, fetchInvoices } = useInvoices();
const { customers: customerList, fetchCustomers } = useCustomers();
const { products: productList, fetchProducts } = useProducts();

const toast = useToast();
const search = ref("");
const currentPage = ref(1);
const showModal = ref(false);
const saving = ref(false);
const errors = ref<Record<string, string>>({});
const debouncedSearch = useDebounce(search);

watch(debouncedSearch, () => {
  currentPage.value = 1;
});

watch(
  [debouncedSearch, currentPage],
  () => {
    fetchPayments({
      search: debouncedSearch.value,
      page: currentPage.value,
      per_page: 10,
    });
  },
  { immediate: true },
);

watch(showModal, (val) => {
  if (val) {
    fetchInvoices({ per_page: 100 });
    fetchCustomers({ per_page: 100 });
    fetchProducts({ per_page: 100 });
  }
});

function handleInvoiceCreated(invoice: any) {
  // Refresh invoice list so the new invoice appears in the dropdown
  fetchInvoices({ per_page: 100 });
}

async function handleSubmit(data: PaymentFormData) {
  saving.value = true;
  errors.value = {};
  try {
    await createPayment(data);
    toast.success("Payment recorded.");
    showModal.value = false;
    await fetchPayments({
      search: debouncedSearch.value,
      page: currentPage.value,
      per_page: 10,
    });
  } catch (e: any) {
    if (e?.data?.errors) errors.value = e.data.errors;
    toast.apiError(e, "Failed to record payment.");
  } finally {
    saving.value = false;
  }
}
</script>
