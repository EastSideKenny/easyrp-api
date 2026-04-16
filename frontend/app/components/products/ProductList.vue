<template>
  <div class="space-y-6">
    <!-- Toolbar -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <UiAppSearchInput
        v-model="search"
        placeholder="Search products…"
        class="w-full sm:w-80"
      />
      <UiAppButton
        size="xs"
        :disabled="atLimit"
        :title="
          atLimit ? 'Product limit reached — upgrade to add more' : undefined
        "
        @click="$emit('create')"
      >
        <Plus class="w-3.5 h-3.5" /> New Product
      </UiAppButton>
    </div>

    <!-- Usage meter — hidden when unlimited (Pro) -->
    <UiAppUsageMeter :usage="productUsage" label="Products" />

    <!-- Table -->
    <UiAppDataTable
      :columns="columns"
      :rows="products"
      :loading="loading"
      :sort-by="sortBy"
      :sort-dir="sortDir"
      clickable
      @sort="handleSort"
      @row-click="(row: Product) => $emit('view', row.id)"
    >
      <template #row="{ row }: { row: Product }">
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-xl bg-surface-alt border border-border flex items-center justify-center shrink-0"
            >
              <Package class="w-5 h-5 text-text-muted" />
            </div>
            <div>
              <p class="font-semibold text-text">{{ row.name }}</p>
              <p class="text-xs text-text-muted">{{ row.sku }}</p>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 text-text-secondary capitalize">{{ row.type }}</td>
        <td class="px-6 py-4 font-medium text-text tabular-nums">
          {{ formatCurrency(row.price) }}
        </td>
        <td class="px-6 py-4 tabular-nums">
          <span
            :class="
              row.stock_quantity <= row.low_stock_threshold
                ? 'text-danger font-semibold'
                : 'text-text'
            "
          >
            {{ row.stock_quantity }}
          </span>
        </td>
        <td class="px-6 py-4">
          <UiAppBadge :variant="row.is_active ? 'success' : 'neutral'">
            {{ row.is_active ? "Active" : "Inactive" }}
          </UiAppBadge>
        </td>
      </template>

      <template #empty>
        <UiAppEmptyState
          :icon="Package"
          title="No products yet"
          description="Create your first product to get started."
        >
          <template #action>
            <UiAppButton size="xs" @click="$emit('create')">
              <Plus class="w-3.5 h-3.5" /> New Product
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
import type { Product, PaginationMeta, TableColumn } from "~/types";
import { Package, Plus } from "lucide-vue-next";

defineProps<{
  products: Product[];
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
const productUsage = computed(() => usageFor("products"));
const atLimit = computed(() => isAtLimit("products"));

const search = defineModel<string>("search", { default: "" });
const sortBy = ref("name");
const sortDir = ref<"asc" | "desc">("asc");

const columns: TableColumn[] = [
  { key: "name", label: "Product", sortable: true },
  { key: "type", label: "Type", sortable: true },
  { key: "price", label: "Price", sortable: true },
  { key: "stock_quantity", label: "Stock", sortable: true },
  { key: "is_active", label: "Status" },
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
