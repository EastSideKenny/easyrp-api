<template>
  <NuxtLayout name="default" page-title="Customer Details">
    <div
      v-if="loading"
      class="flex items-center justify-center py-20 text-text-muted"
    >
      Loading…
    </div>
    <CustomersCustomerDetail
      v-else
      :customer="customer"
      :orders="customerOrders"
      :invoices="customerInvoices"
      :payments="customerPayments"
      :loading-orders="loadingOrders"
      :loading-invoices="loadingInvoices"
      :loading-payments="loadingPayments"
      @edit="showEditModal = true"
      @delete="handleDelete"
    />

    <!-- Edit Modal -->
    <UiAppModal v-model="showEditModal" title="Edit Customer" size="xl">
      <CustomersCustomerForm
        v-if="customer"
        :initial-data="customer"
        :saving="saving"
        submit-label="Update Customer"
        :errors="errors"
        @submit="handleUpdate"
        @cancel="showEditModal = false"
      />
    </UiAppModal>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { CustomerFormData, Order, Invoice, Payment } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth"],
});

const route = useRoute();
const customerId = computed(() => Number(route.params.id));

const { customer, loading, fetchCustomer, updateCustomer, deleteCustomer } =
  useCustomers();

const toast = useToast();
const showEditModal = ref(false);
const saving = ref(false);
const errors = ref<Record<string, string>>({});

// ── Related data ──
const api = useApi();

const customerOrders = ref<Order[]>([]);
const customerInvoices = ref<Invoice[]>([]);
const customerPayments = ref<Payment[]>([]);
const loadingOrders = ref(false);
const loadingInvoices = ref(false);
const loadingPayments = ref(false);

type LaravelPage<T> = { data: T[]; current_page: number; total: number };
type ApiList<T> = T[] | LaravelPage<T> | { data: T[] };

function extractList<T>(res: ApiList<T>): T[] {
  if (Array.isArray(res)) return res;
  if ("data" in res && Array.isArray(res.data)) return res.data;
  return [];
}

async function loadRelated(id: number) {
  loadingOrders.value = true;
  loadingInvoices.value = true;
  loadingPayments.value = true;

  await Promise.all([
    api<ApiList<Order>>(`/api/orders?customer_id=${id}&per_page=100`)
      .then((res) => {
        customerOrders.value = extractList(res);
      })
      .catch((e) => {
        console.error("orders failed", e);
      })
      .finally(() => {
        loadingOrders.value = false;
      }),

    api<ApiList<Invoice>>(`/api/invoices?customer_id=${id}&per_page=100`)
      .then((res) => {
        customerInvoices.value = extractList(res);
      })
      .catch((e) => {
        console.error("invoices failed", e);
      })
      .finally(() => {
        loadingInvoices.value = false;
      }),

    api<ApiList<Payment>>(`/api/payments?customer_id=${id}&per_page=100`)
      .then((res) => {
        customerPayments.value = extractList(res);
      })
      .catch((e) => {
        console.error("payments failed", e);
      })
      .finally(() => {
        loadingPayments.value = false;
      }),
  ]);
}

await Promise.all([
  fetchCustomer(customerId.value),
  loadRelated(customerId.value),
]);

async function handleUpdate(data: CustomerFormData) {
  saving.value = true;
  errors.value = {};
  try {
    await updateCustomer(customerId.value, data);
    toast.success("Customer updated.");
    showEditModal.value = false;
    await fetchCustomer(customerId.value);
  } catch (e: any) {
    if (e?.data?.errors) errors.value = e.data.errors;
    toast.apiError(e, "Failed to update customer.");
  } finally {
    saving.value = false;
  }
}

async function handleDelete() {
  if (
    customerOrders.value.length > 0 ||
    customerInvoices.value.length > 0 ||
    customerPayments.value.length > 0
  ) {
    toast.error(
      "This customer has linked orders, invoices, or payments and cannot be deleted.",
    );
    return;
  }
  if (!confirm("Delete this customer?")) return;
  try {
    await deleteCustomer(customerId.value);
    toast.success("Customer deleted.");
    navigateTo("/customers");
  } catch (e: any) {
    toast.apiError(e, "Failed to delete customer.");
  }
}
</script>
