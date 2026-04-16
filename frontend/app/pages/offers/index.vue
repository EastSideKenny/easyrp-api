<template>
  <NuxtLayout name="default" page-title="Offers">
    <OffersOfferList
      v-model:search="search"
      v-model:status-filter="statusFilter"
      :offers="offers"
      :loading="loading"
      :meta="meta"
      @create="navigateTo('/offers/new')"
      @view="(id) => navigateTo(`/offers/${id}`)"
      @page-change="onPageChange"
    />
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "invoices",
});

const { offers, loading, meta, fetchOffers } = useOffers();
const search = ref("");
const statusFilter = ref("");
const currentPage = ref(1);

async function load() {
  await fetchOffers({
    search: search.value,
    status: statusFilter.value,
    page: currentPage.value,
    per_page: 10,
  });
}

watch([search, statusFilter], () => {
  currentPage.value = 1;
  load();
});

function onPageChange(page: number) {
  currentPage.value = page;
  load();
}

await load();
</script>
