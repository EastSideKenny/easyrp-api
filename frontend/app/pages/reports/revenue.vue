<template>
  <NuxtLayout name="default" page-title="Revenue Report">
    <ReportsRevenueReport
      :data="data"
      :loading="loading"
      v-model:from="dateFrom"
      v-model:to="dateTo"
    />
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { RevenueReport } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "reports",
});

const { fetchRevenueReport, loading } = useReports();
const data = ref<RevenueReport[]>([]);

const dateTo = ref(new Date().toISOString().slice(0, 10));
const dateFrom = ref(
  new Date(Date.now() - 30 * 86400000).toISOString().slice(0, 10),
);

watch(
  [dateFrom, dateTo],
  async () => {
    data.value = await fetchRevenueReport({
      from: dateFrom.value,
      to: dateTo.value,
      group_by: "month",
    });
  },
  { immediate: true },
);
</script>
