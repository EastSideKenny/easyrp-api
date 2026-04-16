<template>
  <div v-if="customer" class="space-y-6">
    <div class="flex items-start justify-between">
      <div class="flex items-center gap-4">
        <div
          class="w-14 h-14 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl font-bold"
        >
          {{ customer.name.charAt(0).toUpperCase() }}
        </div>
        <div>
          <h2 class="text-xl font-bold text-text">{{ customer.name }}</h2>
          <p class="text-sm text-text-muted">{{ customer.email }}</p>
        </div>
      </div>
      <div class="flex items-center gap-1.5">
        <UiAppButton variant="ghost" size="xs" @click="$emit('edit')">
          <Pencil class="w-3.5 h-3.5" /> Edit
        </UiAppButton>
        <UiAppButton
          v-if="!hasRelatedRecords"
          variant="ghost"
          size="xs"
          class="text-danger! hover:bg-danger/10!"
          @click="$emit('delete')"
        >
          <Trash2 class="w-3.5 h-3.5" /> Delete
        </UiAppButton>
        <span
          v-else
          class="text-xs text-text-muted italic"
          title="Customers with orders, invoices, or payments cannot be deleted"
          >Cannot delete — has linked records</span
        >
      </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
      <UiAppStatCard
        label="Customer Since"
        :value="new Date(customer.created_at).toLocaleDateString()"
      />
      <UiAppStatCard label="Total Spent" :value="formatCurrency(totalSpent)" />
      <UiAppStatCard
        label="Amount Due"
        :value="formatCurrency(totalDue)"
        :change="totalDue > 0 ? 'Outstanding' : 'All clear'"
        :trending="totalDue === 0"
      />
    </div>

    <div class="grid sm:grid-cols-2 gap-6">
      <UiAppCard title="Contact">
        <dl class="space-y-3 text-sm">
          <div>
            <dt class="text-text-muted">Email</dt>
            <dd class="font-medium text-text">{{ customer.email }}</dd>
          </div>
          <div>
            <dt class="text-text-muted">Phone</dt>
            <dd class="font-medium text-text">{{ customer.phone || "—" }}</dd>
          </div>
          <div>
            <dt class="text-text-muted">Tax Number</dt>
            <dd class="font-medium text-text">
              {{ customer.tax_number || "—" }}
            </dd>
          </div>
        </dl>
      </UiAppCard>

      <UiAppCard title="Address">
        <div class="text-sm text-text">
          <p v-if="customer.address_line_1">{{ customer.address_line_1 }}</p>
          <p v-if="customer.address_line_2">{{ customer.address_line_2 }}</p>
          <p>
            <template v-if="customer.city">{{ customer.city }}, </template>
            <template v-if="customer.postal_code">{{
              customer.postal_code
            }}</template>
          </p>
          <p v-if="customer.country">{{ customer.country }}</p>
          <p v-if="!customer.address_line_1" class="text-text-muted">
            No address on file
          </p>
        </div>
      </UiAppCard>
    </div>

    <div
      v-if="customer.notes"
      class="bg-surface-alt border border-border rounded-2xl p-5"
    >
      <p
        class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2"
      >
        Notes
      </p>
      <p class="text-sm text-text">{{ customer.notes }}</p>
    </div>

    <!-- ── Tabs: Orders / Invoices / Payments ── -->
    <div class="border-b border-border">
      <nav class="-mb-px flex gap-6">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          class="pb-3 text-sm font-medium border-b-2 transition-colors"
          :class="
            activeTab === tab.key
              ? 'border-primary text-primary'
              : 'border-transparent text-text-muted hover:text-text'
          "
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
          <span
            class="ml-1.5 text-xs bg-surface-alt text-text-muted rounded-full px-1.5 py-0.5"
            >{{ tab.count }}</span
          >
        </button>
      </nav>
    </div>

    <!-- Orders tab -->
    <div v-if="activeTab === 'orders'">
      <div
        v-if="loadingOrders"
        class="py-8 text-center text-sm text-text-muted"
      >
        Loading…
      </div>
      <UiAppEmptyState
        v-else-if="!orders.length"
        title="No orders yet"
        description="Orders placed by this customer will appear here."
      />
      <div v-else class="space-y-4">
        <div
          class="divide-y divide-border border border-border rounded-2xl overflow-hidden"
        >
          <div
            v-for="o in paginatedOrders"
            :key="o.id"
            class="flex items-center justify-between px-4 py-3 bg-surface hover:bg-surface-alt transition-colors cursor-pointer"
            @click="navigateTo(`/orders/${o.id}`)"
          >
            <div>
              <p class="text-sm font-semibold text-text">
                {{ o.order_number }}
              </p>
              <p class="text-xs text-text-muted">
                {{ new Date(o.created_at).toLocaleDateString() }}
              </p>
            </div>
            <div class="flex items-center gap-3">
              <UiAppBadge :variant="orderVariant(o.status)">{{
                o.status
              }}</UiAppBadge>
              <span class="text-sm font-semibold text-text tabular-nums">{{
                formatCurrency(Number(o.total))
              }}</span>
            </div>
          </div>
        </div>
        <UiAppPagination
          :current-page="ordersMeta.current_page"
          :total-pages="ordersMeta.last_page"
          :total="ordersMeta.total"
          :from="ordersMeta.from"
          :to="ordersMeta.to"
          @update:current-page="(p: number) => (ordersPage = p)"
        />
      </div>
    </div>

    <!-- Invoices tab -->
    <div v-if="activeTab === 'invoices'">
      <div
        v-if="loadingInvoices"
        class="py-8 text-center text-sm text-text-muted"
      >
        Loading…
      </div>
      <UiAppEmptyState
        v-else-if="!invoices.length"
        title="No invoices yet"
        description="Invoices issued to this customer will appear here."
      />
      <div v-else class="space-y-4">
        <div
          class="divide-y divide-border border border-border rounded-2xl overflow-hidden"
        >
          <div
            v-for="inv in paginatedInvoices"
            :key="inv.id"
            class="flex items-center justify-between px-4 py-3 bg-surface hover:bg-surface-alt transition-colors cursor-pointer"
            @click="navigateTo(`/invoices/${inv.id}`)"
          >
            <div>
              <p class="text-sm font-semibold text-text">
                {{ inv.invoice_number }}
              </p>
              <p class="text-xs text-text-muted">
                Due {{ new Date(inv.due_date).toLocaleDateString() }}
              </p>
            </div>
            <div class="flex items-center gap-3">
              <UiAppBadge :variant="invoiceVariant(inv.status)">{{
                inv.status
              }}</UiAppBadge>
              <span class="text-sm font-semibold text-text tabular-nums">{{
                formatCurrency(Number(inv.total))
              }}</span>
            </div>
          </div>
        </div>
        <UiAppPagination
          :current-page="invoicesMeta.current_page"
          :total-pages="invoicesMeta.last_page"
          :total="invoicesMeta.total"
          :from="invoicesMeta.from"
          :to="invoicesMeta.to"
          @update:current-page="(p: number) => (invoicesPage = p)"
        />
      </div>
    </div>

    <!-- Payments tab -->
    <div v-if="activeTab === 'payments'">
      <div
        v-if="loadingPayments"
        class="py-8 text-center text-sm text-text-muted"
      >
        Loading…
      </div>
      <UiAppEmptyState
        v-else-if="!payments.length"
        title="No payments yet"
        description="Payments from this customer will appear here."
      />
      <div v-else class="space-y-4">
        <div
          class="divide-y divide-border border border-border rounded-2xl overflow-hidden"
        >
          <div
            v-for="p in paginatedPayments"
            :key="p.id"
            class="flex items-center justify-between px-4 py-3 bg-surface hover:bg-surface-alt transition-colors cursor-pointer"
            @click="navigateTo(`/payments/${p.id}`)"
          >
            <div>
              <p class="text-sm font-semibold text-text">
                {{ p.invoice?.invoice_number ?? `Invoice #${p.invoice_id}` }}
              </p>
              <p class="text-xs text-text-muted">
                {{ new Date(p.paid_at).toLocaleDateString() }} ·
                {{ p.payment_method }}
              </p>
            </div>
            <span class="text-sm font-semibold text-text tabular-nums">{{
              formatCurrency(Number(p.amount))
            }}</span>
          </div>
        </div>
        <UiAppPagination
          :current-page="paymentsMeta.current_page"
          :total-pages="paymentsMeta.last_page"
          :total="paymentsMeta.total"
          :from="paymentsMeta.from"
          :to="paymentsMeta.to"
          @update:current-page="(p: number) => (paymentsPage = p)"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type {
  Customer,
  Order,
  Invoice,
  Payment,
  BadgeVariant,
  InvoiceStatus,
  OrderStatus,
} from "~/types";
import { Pencil, Trash2 } from "lucide-vue-next";

const props = defineProps<{
  customer: Customer | null;
  orders: Order[];
  invoices: Invoice[];
  payments: Payment[];
  loadingOrders?: boolean;
  loadingInvoices?: boolean;
  loadingPayments?: boolean;
}>();

defineEmits<{
  edit: [];
  delete: [];
}>();

const { formatCurrency } = useCurrency();

const hasRelatedRecords = computed(
  () =>
    props.orders.length > 0 ||
    props.invoices.length > 0 ||
    props.payments.length > 0,
);

const activeTab = ref<"orders" | "invoices" | "payments">("orders");

// ── Client-side pagination (10 per page) ──
const PAGE_SIZE = 4;

const ordersPage = ref(1);
const invoicesPage = ref(1);
const paymentsPage = ref(1);

const paginatedOrders = computed(() => {
  const start = (ordersPage.value - 1) * PAGE_SIZE;
  return props.orders.slice(start, start + PAGE_SIZE);
});
const ordersMeta = computed(() => {
  const total = props.orders.length;
  const lastPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
  const from = total === 0 ? 0 : (ordersPage.value - 1) * PAGE_SIZE + 1;
  const to = Math.min(ordersPage.value * PAGE_SIZE, total);
  return {
    current_page: ordersPage.value,
    last_page: lastPage,
    per_page: PAGE_SIZE,
    total,
    from,
    to,
  };
});

const paginatedInvoices = computed(() => {
  const start = (invoicesPage.value - 1) * PAGE_SIZE;
  return props.invoices.slice(start, start + PAGE_SIZE);
});
const invoicesMeta = computed(() => {
  const total = props.invoices.length;
  const lastPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
  const from = total === 0 ? 0 : (invoicesPage.value - 1) * PAGE_SIZE + 1;
  const to = Math.min(invoicesPage.value * PAGE_SIZE, total);
  return {
    current_page: invoicesPage.value,
    last_page: lastPage,
    per_page: PAGE_SIZE,
    total,
    from,
    to,
  };
});

const paginatedPayments = computed(() => {
  const start = (paymentsPage.value - 1) * PAGE_SIZE;
  return props.payments.slice(start, start + PAGE_SIZE);
});
const paymentsMeta = computed(() => {
  const total = props.payments.length;
  const lastPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
  const from = total === 0 ? 0 : (paymentsPage.value - 1) * PAGE_SIZE + 1;
  const to = Math.min(paymentsPage.value * PAGE_SIZE, total);
  return {
    current_page: paymentsPage.value,
    last_page: lastPage,
    per_page: PAGE_SIZE,
    total,
    from,
    to,
  };
});

const totalSpent = computed(() =>
  props.payments.reduce((sum, p) => sum + Number(p.amount), 0),
);

const totalDue = computed(() =>
  props.invoices
    .filter((inv) => inv.status !== "paid" && inv.status !== "canceled")
    .reduce((sum, inv) => {
      const paid = (inv.payments ?? []).reduce(
        (s, p) => s + Number(p.amount),
        0,
      );
      return sum + Math.max(0, Number(inv.total) - paid);
    }, 0),
);

const tabs = computed(() => [
  { key: "orders" as const, label: "Orders", count: props.orders.length },
  { key: "invoices" as const, label: "Invoices", count: props.invoices.length },
  { key: "payments" as const, label: "Payments", count: props.payments.length },
]);

function orderVariant(status: OrderStatus): BadgeVariant {
  const map: Record<OrderStatus, BadgeVariant> = {
    pending: "warning",
    paid: "success",
    done: "info",
    canceled: "danger",
  };
  return map[status];
}

function invoiceVariant(status: InvoiceStatus): BadgeVariant {
  const map: Record<InvoiceStatus, BadgeVariant> = {
    draft: "neutral",
    sent: "info",
    paid: "success",
    canceled: "danger",
  };
  return map[status];
}
</script>
