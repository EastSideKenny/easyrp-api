<template>
  <NuxtLayout name="default" page-title="Invoices">
    <InvoicesInvoiceList
      :invoices="invoices"
      :loading="loading"
      :meta="meta"
      v-model:search="search"
      v-model:status-filter="statusFilter"
      @create="navigateTo('/invoices/new')"
      @view="(id: number) => navigateTo(`/invoices/${id}`)"
      @page-change="(page: number) => (currentPage = page)"
    />
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "invoices",
});

const { invoices, loading, meta, fetchInvoices } = useInvoices();
const search = ref("");
const statusFilter = ref("");
const currentPage = ref(1);
const debouncedSearch = useDebounce(search);

watch([debouncedSearch, statusFilter], () => {
  currentPage.value = 1;
});

watch(
  [debouncedSearch, statusFilter, currentPage],
  () => {
    fetchInvoices({
      search: debouncedSearch.value,
      status: statusFilter.value || undefined,
      page: currentPage.value,
      per_page: 10,
    });
  },
  { immediate: true },
);
</script>
