<template>
  <NuxtLayout name="default" page-title="Customers">
    <CustomersCustomerList
      :customers="customers"
      :loading="loading"
      :meta="meta"
      v-model:search="search"
      @create="navigateTo('/customers/new')"
      @view="(id: number) => navigateTo(`/customers/${id}`)"
      @page-change="(page: number) => (currentPage = page)"
    />
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ["auth"],
});

const { customers, loading, meta, fetchCustomers } = useCustomers();
const search = ref("");
const currentPage = ref(1);
const debouncedSearch = useDebounce(search);

watch(debouncedSearch, () => {
  currentPage.value = 1;
});

watch(
  [debouncedSearch, currentPage],
  () => {
    fetchCustomers({
      search: debouncedSearch.value,
      page: currentPage.value,
      per_page: 10,
    });
  },
  { immediate: true },
);
</script>
