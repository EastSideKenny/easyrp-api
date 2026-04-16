<template>
  <NuxtLayout name="default" page-title="Inventory">
    <InventoryStockOverview
      :items="stockItems"
      :loading="loading"
      :meta="meta"
      v-model:search="search"
      @adjust="showAdjustModal = true"
      @view="(id: number) => navigateTo(`/products/${id}`)"
      @page-change="(page: number) => (currentPage = page)"
    />

    <!-- Movement Modal -->
    <UiAppModal
      v-model="showAdjustModal"
      title="Record Stock Movement"
      size="lg"
    >
      <InventoryStockAdjustmentForm
        :products="stockItems"
        :saving="saving"
        :errors="errors"
        @submit="handleMovement"
        @cancel="showAdjustModal = false"
      />
    </UiAppModal>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { StockMovementFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "products",
});

const { stockItems, loading, meta, fetchStock, createMovement } =
  useInventory();

const search = ref("");
const currentPage = ref(1);
const showAdjustModal = ref(false);
const saving = ref(false);
const errors = ref<Record<string, string>>({});
const debouncedSearch = useDebounce(search);

watch(
  [debouncedSearch, currentPage],
  () => {
    fetchStock({
      search: debouncedSearch.value,
      page: currentPage.value,
      per_page: 10,
    });
  },
  { immediate: true },
);

async function handleMovement(data: StockMovementFormData) {
  saving.value = true;
  errors.value = {};
  try {
    await createMovement(data);
    showAdjustModal.value = false;
    await fetchStock({
      search: debouncedSearch.value,
      page: currentPage.value,
      per_page: 10,
    });
  } catch (e: any) {
    if (e?.data?.errors) errors.value = e.data.errors;
  } finally {
    saving.value = false;
  }
}
</script>
