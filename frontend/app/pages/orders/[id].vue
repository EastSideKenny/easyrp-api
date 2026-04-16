<template>
  <NuxtLayout name="default" page-title="Order Details">
    <div
      v-if="loading"
      class="flex items-center justify-center py-20 text-text-muted"
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
    <OrdersOrderDetail
      v-else
      :order="order"
      @edit="openEditModal"
      @mark-done="handleMarkDone"
      @mark-paid="handleMarkPaid"
      @cancel-order="handleCancel"
      @create-invoice="handleCreateInvoice"
    />

    <!-- Edit Modal -->
    <UiAppModal v-model="showEditModal" title="Edit Order" size="xl">
      <div
        v-if="editLoading"
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
        v-else-if="order"
        :customers="customers"
        :products="products"
        :initial-data="orderFormData"
        :saving="editSaving"
        submit-label="Update Order"
        :errors="editErrors"
        @submit="handleUpdate"
        @cancel="showEditModal = false"
      />
    </UiAppModal>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { OrderFormData, InvoiceFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth"],
});

const route = useRoute();
const { order, loading, fetchOrder, updateOrder } = useOrders();
const { createInvoice } = useInvoices();
const { customers, fetchCustomers } = useCustomers();
const { products, fetchProducts } = useProducts();
const toast = useToast();

const showEditModal = ref(false);
const editLoading = ref(false);
const editSaving = ref(false);
const editErrors = ref<Record<string, string>>({});

const orderId = computed(() => Number(route.params.id));

onMounted(() => fetchOrder(orderId.value));

// Map Order to form data shape
const orderFormData = computed<Partial<OrderFormData>>(() => {
  if (!order.value) return {};
  return {
    customer_id: order.value.customer_id,
    status: order.value.status,
    items: (order.value.items ?? []).map((item) => ({
      product_id: item.product_id,
      description: item.description ?? item.product?.name ?? "",
      quantity: item.quantity,
      unit_price: Number(item.unit_price),
      tax_rate: Number(item.tax_rate),
      line_total: Number(item.line_total),
    })),
  };
});

async function openEditModal() {
  showEditModal.value = true;
  editErrors.value = {};
  if (!customers.value.length || !products.value.length) {
    editLoading.value = true;
    await Promise.all([
      fetchCustomers({ per_page: 100 }),
      fetchProducts({ per_page: 100 }),
    ]);
    editLoading.value = false;
  }
}

async function handleUpdate(
  data: OrderFormData & { subtotal: number; tax_total: number; total: number },
) {
  editSaving.value = true;
  editErrors.value = {};
  try {
    await updateOrder(orderId.value, data);
    toast.success("Order updated.");
    showEditModal.value = false;
    await fetchOrder(orderId.value);
  } catch (e: any) {
    if (e?.response?._data?.errors) {
      const fieldErrors: Record<string, string> = {};
      for (const [key, messages] of Object.entries(e.response._data.errors)) {
        fieldErrors[key] = (messages as string[])[0] ?? "Invalid";
      }
      editErrors.value = fieldErrors;
    }
    toast.apiError(e, "Failed to update order.");
  } finally {
    editSaving.value = false;
  }
}

async function handleMarkPaid() {
  try {
    await updateOrder(orderId.value, {
      status: "paid",
      payment_status: "paid",
    });
    toast.success("Order marked as paid.");
    await fetchOrder(orderId.value);
  } catch (e: any) {
    toast.apiError(e, "Failed to update order.");
  }
}

async function handleMarkDone() {
  try {
    await updateOrder(orderId.value, { status: "done" });
    toast.success("Order marked as done.");
    await fetchOrder(orderId.value);
  } catch (e: any) {
    toast.apiError(e, "Failed to update order.");
  }
}

async function handleCancel() {
  if (!confirm("Are you sure you want to cancel this order?")) return;
  try {
    await updateOrder(orderId.value, { status: "canceled" });
    toast.success("Order canceled.");
    await fetchOrder(orderId.value);
  } catch (e: any) {
    toast.apiError(e, "Failed to cancel order.");
  }
}

async function handleCreateInvoice() {
  if (!order.value) return;
  const today = new Date().toISOString().slice(0, 10);
  const dueDateObj = new Date();
  dueDateObj.setDate(dueDateObj.getDate() + 30);
  const dueDate = dueDateObj.toISOString().slice(0, 10);

  const invoiceData: InvoiceFormData = {
    customer_id: order.value.customer_id,
    order_id: order.value.id,
    status: "draft",
    issue_date: today,
    due_date: dueDate,
    currency: order.value.currency,
    items: (order.value.items ?? []).map((item) => ({
      product_id: item.product_id,
      description: item.description ?? item.product?.name ?? "",
      quantity: item.quantity,
      unit_price: Number(item.unit_price),
      tax_rate: Number(item.tax_rate),
      line_total: Number(item.line_total),
    })),
  };

  try {
    const invoice = await createInvoice(invoiceData);
    toast.success("Invoice created from order.");
    navigateTo(`/invoices/${invoice.id}`);
  } catch (e: any) {
    toast.apiError(e, "Failed to create invoice. Please try again.");
  }
}
</script>
