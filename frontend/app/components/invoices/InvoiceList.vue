<template>
  <div class="space-y-6">
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div class="flex items-center gap-3 w-full sm:w-auto">
        <UiAppSearchInput
          v-model="search"
          placeholder="Search invoices…"
          class="flex-1 sm:w-80"
        />
        <select
          v-model="statusFilter"
          class="border border-border rounded-xl px-3 py-2.5 text-sm bg-surface-alt focus:outline-none focus:border-primary transition-all"
        >
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="sent">Sent</option>
          <option value="paid">Paid</option>
          <option value="canceled">Canceled</option>
        </select>
      </div>
      <UiAppButton
        size="xs"
        :disabled="atLimit"
        :title="
          atLimit ? 'Invoice limit reached — upgrade to add more' : undefined
        "
        @click="$emit('create')"
      >
        <Plus class="w-3.5 h-3.5" /> New Invoice
      </UiAppButton>
    </div>

    <!-- Usage meter — hidden when unlimited (Pro) -->
    <UiAppUsageMeter :usage="invoiceUsage" label="Invoices" />

    <UiAppDataTable
      :columns="columns"
      :rows="invoices"
      :loading="loading"
      :sort-by="sortBy"
      :sort-dir="sortDir"
      clickable
      @sort="handleSort"
      @row-click="(row: Invoice) => $emit('view', row.id)"
    >
      <template #row="{ row }: { row: Invoice }">
        <td class="px-6 py-4 font-semibold text-primary">
          {{ row.invoice_number }}
        </td>
        <td class="px-6 py-4 text-text">{{ row.customer?.name ?? "—" }}</td>
        <td class="px-6 py-4 text-text-secondary text-sm">
          {{ new Date(row.issue_date).toLocaleDateString() }}
        </td>
        <td class="px-6 py-4 text-text-secondary text-sm">
          {{ new Date(row.due_date).toLocaleDateString() }}
        </td>
        <td class="px-6 py-4 font-medium text-text tabular-nums">
          {{ formatCurrency(row.total) }}
        </td>
        <td class="px-6 py-4 text-text-secondary text-sm">
          {{ row.currency }}
        </td>
        <td class="px-6 py-4">
          <UiAppBadge :variant="statusVariant(row.status)">{{
            row.status
          }}</UiAppBadge>
        </td>
      </template>

      <template #empty>
        <UiAppEmptyState
          :icon="FileTextIcon"
          title="No invoices yet"
          description="Create your first invoice to start billing."
        >
          <template #action>
            <UiAppButton size="xs" @click="$emit('create')">
              <Plus class="w-3.5 h-3.5" /> New Invoice
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
  Invoice,
  InvoiceStatus,
  PaginationMeta,
  TableColumn,
  BadgeVariant,
} from "~/types";
import { FileText as FileTextIcon, Plus } from "lucide-vue-next";

defineProps<{
  invoices: Invoice[];
  loading: boolean;
  meta: PaginationMeta | null;
}>();

defineEmits<{
  create: [];
  view: [id: number];
  "page-change": [page: number];
}>();

const { formatCurrency } = useCurrency();
const { usageFor, isAtLimit } = useSubscription();
const invoiceUsage = computed(() => usageFor("invoices"));
const atLimit = computed(() => isAtLimit("invoices"));

const search = defineModel<string>("search", { default: "" });
const statusFilter = defineModel<string>("statusFilter", { default: "" });
const sortBy = ref("issue_date");
const sortDir = ref<"asc" | "desc">("desc");

const columns: TableColumn[] = [
  { key: "invoice_number", label: "Invoice #", sortable: true },
  { key: "customer", label: "Customer", sortable: true },
  { key: "issue_date", label: "Issued", sortable: true },
  { key: "due_date", label: "Due", sortable: true },
  { key: "total", label: "Total", sortable: true },
  { key: "currency", label: "Currency" },
  { key: "status", label: "Status" },
];

function statusVariant(status: InvoiceStatus): BadgeVariant {
  const map: Record<InvoiceStatus, BadgeVariant> = {
    draft: "info",
    sent: "primary",
    paid: "success",
    canceled: "neutral",
  };
  return map[status];
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
