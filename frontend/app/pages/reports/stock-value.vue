<template>
  <NuxtLayout name="default" page-title="Stock Value Report">
    <ReportsStockValueReport :data="data" :loading="loading" />
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { StockValueReport } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "reports",
});

const { fetchStockValueReport, loading } = useReports();
const data = ref<StockValueReport[]>([]);

onMounted(async () => {
  data.value = await fetchStockValueReport();
});
</script>
