<template>
  <div class="space-y-6">
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <UiAppSearchInput
        v-model="search"
        placeholder="Search customers…"
        class="w-full sm:w-80"
      />
      <UiAppButton
        size="xs"
        :disabled="atLimit"
        :title="
          atLimit ? 'Customer limit reached — upgrade to add more' : undefined
        "
        @click="$emit('create')"
      >
        <Plus class="w-3.5 h-3.5" /> New Customer
      </UiAppButton>
    </div>

    <!-- Usage meter — hidden when unlimited (Pro) -->
    <UiAppUsageMeter :usage="customerUsage" label="Customers" />

    <UiAppDataTable
      :columns="columns"
      :rows="customers"
      :loading="loading"
      :sort-by="sortBy"
      :sort-dir="sortDir"
      clickable
      @sort="handleSort"
      @row-click="(row: Customer) => $emit('view', row.id)"
    >
      <template #row="{ row }: { row: Customer }">
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            <div
              class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-semibold"
            >
              {{ row.name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <p class="font-semibold text-text">{{ row.name }}</p>
              <p class="text-xs text-text-muted">{{ row.email }}</p>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 text-text-secondary">{{ row.phone || "—" }}</td>
        <td class="px-6 py-4 text-text-secondary">{{ row.city || "—" }}</td>
        <td class="px-6 py-4 text-text-secondary">{{ row.country || "—" }}</td>
      </template>

      <template #empty>
        <UiAppEmptyState
          :icon="UsersIcon"
          title="No customers yet"
          description="Add your first customer to start invoicing."
        >
          <template #action>
            <UiAppButton size="xs" @click="$emit('create')">
              <Plus class="w-3.5 h-3.5" /> New Customer
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
import type { Customer, PaginationMeta, TableColumn } from "~/types";
import { Users as UsersIcon, Plus } from "lucide-vue-next";

defineProps<{
  customers: Customer[];
  loading: boolean;
  meta: PaginationMeta | null;
}>();

defineEmits<{
  create: [];
  view: [id: number];
  "page-change": [page: number];
}>();

const { usageFor, isAtLimit } = useSubscription();
const customerUsage = computed(() => usageFor("customers"));
const atLimit = computed(() => isAtLimit("customers"));

const search = defineModel<string>("search", { default: "" });
const sortBy = ref("name");
const sortDir = ref<"asc" | "desc">("asc");

const columns: TableColumn[] = [
  { key: "name", label: "Customer", sortable: true },
  { key: "phone", label: "Phone" },
  { key: "city", label: "City", sortable: true },
  { key: "country", label: "Country", sortable: true },
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
