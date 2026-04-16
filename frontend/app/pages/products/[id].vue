<template>
  <NuxtLayout name="default" page-title="Product Details">
    <div
      v-if="loading"
      class="flex items-center justify-center py-20 text-text-muted"
    >
      Loading…
    </div>
    <ProductsProductDetail
      v-else
      :product="product"
      :has-linked-records="hasLinkedRecords"
      @edit="showEditModal = true"
      @delete="handleDelete"
    />

    <!-- Edit Modal -->
    <UiAppModal v-model="showEditModal" title="Edit Product" size="xl">
      <ProductsProductForm
        v-if="product"
        :initial-data="product"
        :saving="saving"
        submit-label="Update Product"
        :errors="errors"
        @submit="handleUpdate"
        @cancel="showEditModal = false"
      />
    </UiAppModal>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { ProductFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "products",
});

const route = useRoute();
const { product, loading, fetchProduct, updateProduct, deleteProduct } =
  useProducts();

const toast = useToast();
const showEditModal = ref(false);
const saving = ref(false);
const errors = ref<Record<string, string>>({});

await fetchProduct(Number(route.params.id));

// Check if product is used in any orders or invoices
const api = useApi();
const hasLinkedRecords = ref(false);

try {
  const res = await api<{ in_use: boolean }>(
    `/api/products/${route.params.id}/in-use`,
  );
  hasLinkedRecords.value = res.in_use;
} catch {
  // If endpoint doesn't exist yet, fail-open (allow delete)
  hasLinkedRecords.value = false;
}

async function handleUpdate(data: ProductFormData) {
  saving.value = true;
  errors.value = {};
  try {
    await updateProduct(Number(route.params.id), data);
    toast.success("Product updated.");
    showEditModal.value = false;
    await fetchProduct(Number(route.params.id));
  } catch (e: any) {
    if (e?.data?.errors) errors.value = e.data.errors;
    toast.apiError(e, "Failed to update product.");
  } finally {
    saving.value = false;
  }
}

async function handleDelete() {
  if (hasLinkedRecords.value) {
    toast.error(
      "This product is used in orders or invoices and cannot be deleted. Deactivate it instead.",
    );
    return;
  }
  if (!confirm("Delete this product?")) return;
  try {
    await deleteProduct(Number(route.params.id));
    toast.success("Product deleted.");
    navigateTo("/products");
  } catch (e: any) {
    toast.apiError(e, "Failed to delete product.");
  }
}
</script>
