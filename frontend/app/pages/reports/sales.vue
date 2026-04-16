<template>
  <NuxtLayout name="default" page-title="Sales by Product">
    <ReportsSalesByProductReport
      :data="data"
      :loading="loading"
      v-model:from="dateFrom"
      v-model:to="dateTo"
    />
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { SalesByProductReport } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "reports",
});

const { fetchSalesByProduct, loading } = useReports();
const data = ref<SalesByProductReport[]>([]);

const dateTo = ref(new Date().toISOString().slice(0, 10));
const dateFrom = ref(
  new Date(Date.now() - 30 * 86400000).toISOString().slice(0, 10),
);

watch(
  [dateFrom, dateTo],
  async () => {
    data.value = await fetchSalesByProduct({
      from: dateFrom.value,
      to: dateTo.value,
    });
  },
  { immediate: true },
);
</script>
