<template>
  <NuxtLayout name="default" page-title="Invoice Details">
    <div
      v-if="loading"
      class="flex items-center justify-center py-20 text-text-muted"
    >
      Loading…
    </div>
    <InvoicesInvoiceDetail
      v-else
      :invoice="invoice"
      @send="handleSend"
      @record-payment="showPaymentModal = true"
      @edit="showEditModal = true"
      @delete="handleDelete"
      @download="handleDownload"
    />

    <!-- Edit Modal -->
    <UiAppModal v-model="showEditModal" title="Edit Invoice" size="xl">
      <InvoicesInvoiceForm
        v-if="invoice"
        :customers="customers"
        :products="products"
        :initial-data="invoiceFormData"
        :saving="editSaving"
        submit-label="Update Invoice"
        :errors="editErrors"
        @submit="handleUpdate"
        @cancel="showEditModal = false"
      />
    </UiAppModal>

    <!-- Record Payment Modal -->
    <UiAppModal v-model="showPaymentModal" title="Record Payment" size="lg">
      <PaymentsPaymentForm
        :invoices="invoice ? [invoice] : []"
        :preselected-invoice-id="invoice?.id"
        :saving="paymentSaving"
        @submit="handlePayment"
        @cancel="showPaymentModal = false"
      />
    </UiAppModal>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { InvoiceFormData, PaymentFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "invoices",
});

const route = useRoute();
const {
  invoice,
  loading,
  fetchInvoice,
  updateInvoice,
  sendInvoice,
  deleteInvoice,
  downloadInvoicePdf,
} = useInvoices();
const { customers, fetchCustomers } = useCustomers();
const { products, fetchProducts } = useProducts();
const { createPayment } = usePayments();

const showEditModal = ref(false);
const editSaving = ref(false);
const editErrors = ref<Record<string, string>>({});

const showPaymentModal = ref(false);
const paymentSaving = ref(false);
const toast = useToast();

await fetchInvoice(Number(route.params.id));

// Lazy-load customers & products when edit modal opens
watch(showEditModal, async (open) => {
  if (open && customers.value.length === 0) {
    await Promise.all([
      fetchCustomers({ per_page: 100 }),
      fetchProducts({ per_page: 100 }),
    ]);
  }
});

// Map the full Invoice to InvoiceFormData shape for the form
const invoiceFormData = computed<Partial<InvoiceFormData>>(() => {
  if (!invoice.value) return {};
  return {
    customer_id: invoice.value.customer_id,
    status: invoice.value.status,
    issue_date: invoice.value.issue_date,
    due_date: invoice.value.due_date,
    currency: invoice.value.currency,
    items: invoice.value.items.map((item) => ({
      product_id: item.product_id,
      description: item.description,
      quantity: item.quantity,
      unit_price: item.unit_price,
      tax_rate: item.tax_rate,
      line_total: item.line_total,
    })),
  };
});

async function handleUpdate(data: InvoiceFormData) {
  editSaving.value = true;
  editErrors.value = {};
  try {
    await updateInvoice(Number(route.params.id), data);
    toast.success("Invoice updated.");
    showEditModal.value = false;
    await fetchInvoice(Number(route.params.id));
  } catch (e: any) {
    if (e?.data?.errors) editErrors.value = e.data.errors;
    toast.apiError(e, "Failed to update invoice.");
  } finally {
    editSaving.value = false;
  }
}

async function handleSend() {
  try {
    await sendInvoice(Number(route.params.id));
    toast.success("Invoice sent.");
    await fetchInvoice(Number(route.params.id));
  } catch (e: any) {
    toast.apiError(e, "Failed to send invoice.");
  }
}

async function handleDownload() {
  try {
    await downloadInvoicePdf(Number(route.params.id));
  } catch (e: any) {
    toast.apiError(e, "Failed to download invoice PDF.");
  }
}

async function handlePayment(data: PaymentFormData) {
  paymentSaving.value = true;
  try {
    await createPayment(data);
    toast.success("Payment recorded.");
    showPaymentModal.value = false;
    await fetchInvoice(Number(route.params.id));
  } catch (e: any) {
    toast.apiError(e, "Failed to record payment.");
  } finally {
    paymentSaving.value = false;
  }
}

async function handleDelete() {
  if (invoice.value?.status === "sent" || invoice.value?.status === "paid") {
    toast.error("Sent or paid invoices cannot be deleted.");
    return;
  }
  if (!confirm("Delete this invoice?")) return;
  try {
    await deleteInvoice(Number(route.params.id));
    toast.success("Invoice deleted.");
    navigateTo("/invoices");
  } catch (e: any) {
    toast.apiError(e, "Failed to delete invoice.");
  }
}
</script>
