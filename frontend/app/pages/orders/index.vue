<template>
  <NuxtLayout name="default" page-title="Orders">
    <div class="space-y-6">
      <!-- Toolbar -->
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
      >
        <UiAppSearchInput
          v-model="search"
          placeholder="Search orders…"
          class="w-full sm:w-80"
        />
        <UiAppButton
          size="xs"
          :disabled="atLimit"
          :title="
            atLimit ? 'Order limit reached — upgrade to add more' : undefined
          "
          @click="openNewOrder"
        >
          <Plus class="w-3.5 h-3.5" /> New Order
        </UiAppButton>
      </div>

      <!-- Usage meter -->
      <UiAppUsageMeter :usage="orderUsage" label="Orders" />

      <!-- Table -->
      <UiAppDataTable
        :columns="columns"
        :rows="orders"
        :loading="loading"
        clickable
        @row-click="(row: any) => navigateTo(`/orders/${row.id}`)"
      >
        <template #row="{ row }">
          <td class="px-6 py-4 font-semibold text-primary">
            {{ row.order_number }}
          </td>
          <td class="px-6 py-4 text-text">{{ row.customer?.name ?? "—" }}</td>
          <td class="px-6 py-4 text-text-secondary text-sm">
            {{ new Date(row.created_at).toLocaleDateString() }}
          </td>
          <td class="px-6 py-4 font-medium text-text tabular-nums">
            {{ formatCurrency(row.total) }}
          </td>
          <td class="px-6 py-4">
            <UiAppBadge :variant="orderStatusVariant(row.status)">{{
              row.status
            }}</UiAppBadge>
          </td>
          <td class="px-6 py-4 text-text-muted text-sm">
            {{ row.payment_status || "—" }}
          </td>
        </template>

        <template #empty>
          <UiAppEmptyState
            :icon="ShoppingCartIcon"
            title="No orders yet"
            description="Create your first order manually or wait for customers to purchase from your store."
          >
            <template #action>
              <UiAppButton size="xs" :disabled="atLimit" @click="openNewOrder">
                <Plus class="w-3.5 h-3.5" /> New Order
              </UiAppButton>
            </template>
          </UiAppEmptyState>
        </template>

        <template v-if="meta" #pagination>
          <UiAppPagination
            :current-page="meta.current_page"
            :total-pages="meta.last_page"
            :total="meta.total"
            :from="meta.from"
            :to="meta.to"
            @update:current-page="(p: number) => (currentPage = p)"
          />
        </template>
      </UiAppDataTable>
    </div>

    <!-- New Order Modal -->
    <UiAppModal v-model="showModal" title="New Order" size="xl">
      <div
        v-if="modalLoading"
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
        :errors="formErrors"
        @submit="handleCreate"
        @cancel="showModal = false"
      />
    </UiAppModal>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type {
  TableColumn,
  BadgeVariant,
  OrderStatus,
  OrderFormData,
} from "~/types";
import { ShoppingCart as ShoppingCartIcon, Plus } from "lucide-vue-next";

definePageMeta({
  layout: false,
  middleware: ["auth"],
});

const { formatCurrency } = useCurrency();
const { usageFor, isAtLimit } = useSubscription();
const orderUsage = computed(() => usageFor("orders"));
const atLimit = computed(() => isAtLimit("orders"));
const {
  orders,
  loading,
  meta,
  fetchOrders: fetchOrderList,
  createOrder,
} = useOrders();
const { customers, fetchCustomers } = useCustomers();
const { products, fetchProducts } = useProducts();

const search = ref("");
const currentPage = ref(1);

// Modal state
const showModal = ref(false);
const modalLoading = ref(false);
const saving = ref(false);
const formErrors = ref<Record<string, string>>({});

const columns: TableColumn[] = [
  { key: "order_number", label: "Order #", sortable: true },
  { key: "customer", label: "Customer" },
  { key: "created_at", label: "Date", sortable: true },
  { key: "total", label: "Total", sortable: true },
  { key: "status", label: "Status" },
  { key: "payment_status", label: "Payment" },
];

function orderStatusVariant(status: OrderStatus): BadgeVariant {
  const map: Record<OrderStatus, BadgeVariant> = {
    pending: "warning",
    paid: "success",
    done: "info",
    canceled: "danger",
  };
  return map[status];
}

async function openNewOrder() {
  showModal.value = true;
  formErrors.value = {};
  if (!customers.value.length || !products.value.length) {
    modalLoading.value = true;
    await Promise.all([
      fetchCustomers({ per_page: 100 }),
      fetchProducts({ per_page: 100 }),
    ]);
    modalLoading.value = false;
  }
}

async function handleCreate(
  data: OrderFormData & { subtotal: number; tax_total: number; total: number },
) {
  saving.value = true;
  formErrors.value = {};
  try {
    await createOrder(data);
    showModal.value = false;
    // Refresh the list
    await fetchOrderList({
      search: debouncedSearch.value,
      page: currentPage.value,
      per_page: 10,
    });
  } catch (e: any) {
    if (e?.response?._data?.errors) {
      const fieldErrors: Record<string, string> = {};
      for (const [key, messages] of Object.entries(e.response._data.errors)) {
        fieldErrors[key] = (messages as string[])[0] ?? "Invalid";
      }
      formErrors.value = fieldErrors;
    }
  } finally {
    saving.value = false;
  }
}

const debouncedSearch = useDebounce(search);

watch(
  debouncedSearch,
  () => {
    currentPage.value = 1;
  },
  { immediate: false },
);

watch(
  [debouncedSearch, currentPage],
  () => {
    fetchOrderList({
      search: debouncedSearch.value,
      page: currentPage.value,
      per_page: 10,
    });
  },
  { immediate: true },
);
</script>
