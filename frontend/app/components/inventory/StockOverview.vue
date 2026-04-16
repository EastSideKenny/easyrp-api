<template>
  <div class="space-y-6">
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <UiAppSearchInput
        v-model="search"
        placeholder="Search stock…"
        class="w-full sm:w-80"
      />
      <UiAppButton size="xs" @click="$emit('adjust')">
        <Plus class="w-4 h-4" />
        Manual Adjustment
      </UiAppButton>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <UiAppStatCard label="Total SKUs" :value="totalSkus" />
      <UiAppStatCard label="Total Units" :value="formatNumber(totalUnits)" />
      <UiAppStatCard
        label="Low Stock"
        :value="lowStockCount"
        :trending="false"
        :change="`${lowStockCount} items`"
      />
      <UiAppStatCard
        label="Out of Stock"
        :value="outOfStockCount"
        :trending="false"
        :change="`${outOfStockCount} items`"
      />
    </div>

    <!-- Table -->
    <UiAppDataTable
      :columns="columns"
      :rows="items"
      :loading="loading"
      :sort-by="sortBy"
      :sort-dir="sortDir"
      clickable
      @sort="handleSort"
      @row-click="(row: Product) => $emit('view', row.id)"
    >
      <template #row="{ row }: { row: Product }">
        <td class="px-6 py-4">
          <div>
            <p class="font-semibold text-text">{{ row.name }}</p>
            <p class="text-xs text-text-muted">{{ row.sku }}</p>
          </div>
        </td>
        <td class="px-6 py-4 text-text-secondary capitalize">{{ row.type }}</td>
        <td class="px-6 py-4 tabular-nums">
          <span
            class="font-semibold"
            :class="
              row.stock_quantity <= 0
                ? 'text-danger'
                : row.stock_quantity <= row.low_stock_threshold
                  ? 'text-warning'
                  : 'text-success'
            "
          >
            {{ row.stock_quantity }}
          </span>
        </td>
        <td class="px-6 py-4 text-text-muted tabular-nums">
          {{ row.low_stock_threshold }}
        </td>
        <td class="px-6 py-4 font-medium tabular-nums">
          {{ formatCurrency(row.cost_price) }}
        </td>
        <td class="px-6 py-4 font-semibold tabular-nums">
          {{ formatCurrency(row.stock_quantity * row.cost_price) }}
        </td>
        <td class="px-6 py-4">
          <UiAppBadge
            :variant="
              row.stock_quantity <= 0
                ? 'danger'
                : row.stock_quantity <= row.low_stock_threshold
                  ? 'warning'
                  : 'success'
            "
          >
            {{
              row.stock_quantity <= 0
                ? "Out"
                : row.stock_quantity <= row.low_stock_threshold
                  ? "Low"
                  : "OK"
            }}
          </UiAppBadge>
        </td>
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
import { Plus } from "lucide-vue-next";
import type { Product, PaginationMeta, TableColumn } from "~/types";

const props = defineProps<{
  items: Product[];
  loading: boolean;
  meta: PaginationMeta | null;
}>();

defineEmits<{
  adjust: [];
  view: [id: number];
  "page-change": [page: number];
}>();

const { formatCurrency, formatNumber } = useCurrency();

const search = defineModel<string>("search", { default: "" });
const sortBy = ref("name");
const sortDir = ref<"asc" | "desc">("asc");

const totalSkus = computed(() => (props.items ?? []).length);
const totalUnits = computed(() =>
  (props.items ?? []).reduce((s, i) => s + i.stock_quantity, 0),
);
const lowStockCount = computed(
  () =>
    (props.items ?? []).filter(
      (i) => i.stock_quantity > 0 && i.stock_quantity <= i.low_stock_threshold,
    ).length,
);
const outOfStockCount = computed(
  () => (props.items ?? []).filter((i) => i.stock_quantity <= 0).length,
);

const columns: TableColumn[] = [
  { key: "name", label: "Product", sortable: true },
  { key: "type", label: "Type", sortable: true },
  { key: "stock_quantity", label: "Qty", sortable: true },
  { key: "low_stock_threshold", label: "Threshold" },
  { key: "cost_price", label: "Cost", sortable: true },
  { key: "value", label: "Value", sortable: true },
  { key: "status", label: "Status" },
];

function handleSort(key: string) {
  if (sortBy.value === key) {
    sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  } else {
    sortBy.value = key;
    sortDir.value = "asc";
  }
}
</script>
