<template>
  <div class="space-y-6">
    <ReportsReportDateFilter v-model:from="dateFrom" v-model:to="dateTo" />

    <UiAppCard title="Sales by Product" :no-padding="true">
      <div
        v-if="loading"
        class="flex items-center justify-center py-12 text-text-muted text-sm"
      >
        Loading…
      </div>
      <div
        v-else-if="!data.length"
        class="py-12 text-center text-sm text-text-muted"
      >
        No sales data for this period.
      </div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-border bg-surface-alt">
            <th
              class="text-left px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Product
            </th>
            <th
              class="text-left px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              SKU
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Units Sold
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Revenue
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Avg. Price
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Share
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in data"
            :key="row.product_id"
            class="border-b border-border-light last:border-0 hover:bg-surface-alt/60 transition-colors"
          >
            <td class="px-6 py-3 font-medium text-text">
              {{ row.product_name }}
            </td>
            <td class="px-6 py-3 text-text-muted">{{ row.sku }}</td>
            <td class="px-6 py-3 text-right tabular-nums">
              {{ row.units_sold }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums font-medium">
              {{ formatCurrency(row.revenue) }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums">
              {{ formatCurrency(row.avg_price) }}
            </td>
            <td class="px-6 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <div
                  class="w-16 h-1.5 bg-surface-alt rounded-full overflow-hidden"
                >
                  <div
                    class="h-full bg-primary rounded-full"
                    :style="{ width: `${revenueShare(row.revenue)}%` }"
                  />
                </div>
                <span
                  class="text-xs tabular-nums text-text-muted w-10 text-right"
                  >{{ revenueShare(row.revenue).toFixed(1) }}%</span
                >
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </UiAppCard>
  </div>
</template>

<script setup lang="ts">
import type { SalesByProductReport } from "~/types";

const props = defineProps<{
  data: SalesByProductReport[];
  loading: boolean;
}>();

const dateFrom = defineModel<string>("from", { required: true });
const dateTo = defineModel<string>("to", { required: true });

const { formatCurrency } = useCurrency();

const totalRevenue = computed(() =>
  props.data.reduce((s, r) => s + r.revenue, 0),
);

function revenueShare(revenue: number): number {
  return totalRevenue.value > 0 ? (revenue / totalRevenue.value) * 100 : 0;
}
</script>
