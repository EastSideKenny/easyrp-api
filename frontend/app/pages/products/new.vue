<template>
  <NuxtLayout name="default" page-title="New Product">
    <UiAppCard title="Create Product">
      <ProductsProductForm
        :saving="saving"
        submit-label="Create Product"
        :errors="errors"
        @submit="handleSubmit"
        @cancel="navigateTo('/products')"
      />
    </UiAppCard>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { ProductFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "products",
});

const { createProduct } = useProducts();
const toast = useToast();
const saving = ref(false);
const errors = ref<Record<string, string>>({});

async function handleSubmit(data: ProductFormData) {
  saving.value = true;
  errors.value = {};
  try {
    const product = await createProduct(data);
    toast.success("Product created successfully.");
    navigateTo(`/products/${product.id}`);
  } catch (e: any) {
    if (e?.data?.errors) errors.value = e.data.errors;
    toast.apiError(e, "Failed to create product.");
  } finally {
    saving.value = false;
  }
}
</script>
