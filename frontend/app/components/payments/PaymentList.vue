<template>
  <div class="space-y-6">
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <UiAppSearchInput
        v-model="search"
        placeholder="Search payments…"
        class="w-full sm:w-80"
      />
      <UiAppButton size="xs" @click="$emit('create')">
        <Plus class="w-3.5 h-3.5" /> Record Payment
      </UiAppButton>
    </div>

    <UiAppDataTable
      :columns="columns"
      :rows="payments"
      :loading="loading"
      :sort-by="sortBy"
      :sort-dir="sortDir"
      clickable
      @sort="handleSort"
      @row-click="(row: Payment) => $emit('view', row.id)"
    >
      <template #row="{ row }: { row: Payment }">
        <td class="px-6 py-4 font-semibold text-primary">
          {{ row.invoice?.invoice_number ?? `INV-${row.invoice_id}` }}
        </td>
        <td class="px-6 py-4 text-text">
          {{ row.invoice?.customer?.name ?? "—" }}
        </td>
        <td class="px-6 py-4 font-medium text-text tabular-nums">
          {{ formatCurrency(row.amount) }}
        </td>
        <td class="px-6 py-4">
          <UiAppBadge :variant="methodVariant(row.payment_method)">{{
            formatMethod(row.payment_method)
          }}</UiAppBadge>
        </td>
        <td class="px-6 py-4 text-text-secondary text-sm">
          {{ new Date(row.paid_at).toLocaleDateString() }}
        </td>
        <td class="px-6 py-4 text-text-muted text-sm">
          {{ row.transaction_reference || "—" }}
        </td>
      </template>

      <template #empty>
        <UiAppEmptyState
          :icon="CreditCardIcon"
          title="No payments yet"
          description="Record your first payment to get started."
        >
          <template #action>
            <UiAppButton size="xs" @click="$emit('create')">
              <Plus class="w-3.5 h-3.5" /> Record Payment
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
          @update:current-page="$emit('page-change', $event)"
        />
      </template>
    </UiAppDataTable>
  </div>
</template>

<script setup lang="ts">
import type {
  Payment,
  PaymentMethod,
  PaginationMeta,
  TableColumn,
  BadgeVariant,
} from "~/types";
import { CreditCard as CreditCardIcon, Plus } from "lucide-vue-next";

defineProps<{
  payments: Payment[];
  loading: boolean;
  meta: PaginationMeta | null;
}>();

defineEmits<{
  create: [];
  view: [id: number];
  "page-change": [page: number];
}>();

const { formatCurrency } = useCurrency();

const search = defineModel<string>("search", { default: "" });
const sortBy = ref("paid_at");
const sortDir = ref<"asc" | "desc">("desc");

const columns: TableColumn[] = [
  { key: "invoice", label: "Invoice", sortable: true },
  { key: "customer", label: "Customer" },
  { key: "amount", label: "Amount", sortable: true },
  { key: "payment_method", label: "Method" },
  { key: "paid_at", label: "Paid At", sortable: true },
  { key: "transaction_reference", label: "Reference" },
];

function methodVariant(method: PaymentMethod): BadgeVariant {
  const map: Record<PaymentMethod, BadgeVariant> = {
    stripe: "primary",
    bank: "info",
    cash: "success",
  };
  return map[method];
}

function formatMethod(method: PaymentMethod): string {
  return method.replace("_", " ").replace(/\b\w/g, (l) => l.toUpperCase());
}

function handleSort(key: string) {
  if (sortBy.value === key) {
    sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  } else {
    sortBy.value = key;
    sortDir.value = "asc";
  }
}
</script>
