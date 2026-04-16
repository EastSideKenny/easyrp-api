<template>
  <NuxtLayout name="default" page-title="Products">
    <ProductsProductList
      :products="products"
      :loading="loading"
      :meta="meta"
      v-model:search="search"
      @create="navigateTo('/products/new')"
      @view="(id: number) => navigateTo(`/products/${id}`)"
      @page-change="(page: number) => (currentPage = page)"
    />
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "products",
});

const { products, loading, meta, fetchProducts } = useProducts();
const search = ref("");
const currentPage = ref(1);
const debouncedSearch = useDebounce(search);

watch(debouncedSearch, () => {
  currentPage.value = 1;
});

watch(
  [debouncedSearch, currentPage],
  () => {
    fetchProducts({
      search: debouncedSearch.value,
      page: currentPage.value,
      per_page: 10,
    });
  },
  { immediate: true },
);
</script>
