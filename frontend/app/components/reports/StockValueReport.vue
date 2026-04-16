<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid sm:grid-cols-3 gap-4">
      <UiAppStatCard
        label="Total Cost Value"
        :value="formatCurrency(totalCostValue)"
      />
      <UiAppStatCard
        label="Total Retail Value"
        :value="formatCurrency(totalRetailValue)"
      />
      <UiAppStatCard
        label="Potential Profit"
        :value="formatCurrency(totalRetailValue - totalCostValue)"
        :trending="true"
        :change="formatPercentage(margin)"
      />
    </div>

    <UiAppCard title="Stock Value by Product" :no-padding="true">
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
        No inventory data found.
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
              Qty
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Cost
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Cost Value
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Retail Value
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
              {{ row.stock_quantity }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums">
              {{ formatCurrency(row.cost_price) }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums font-medium">
              {{ formatCurrency(row.stock_value) }}
            </td>
            <td
              class="px-6 py-3 text-right tabular-nums font-medium text-success"
            >
              {{ formatCurrency(row.retail_value) }}
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="border-t-2 border-border bg-surface-alt">
            <td
              colspan="4"
              class="px-6 py-3 text-right font-semibold text-text"
            >
              Totals
            </td>
            <td class="px-6 py-3 text-right font-bold tabular-nums text-text">
              {{ formatCurrency(totalCostValue) }}
            </td>
            <td
              class="px-6 py-3 text-right font-bold tabular-nums text-success"
            >
              {{ formatCurrency(totalRetailValue) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </UiAppCard>
  </div>
</template>

<script setup lang="ts">
import type { StockValueReport } from "~/types";

const props = defineProps<{
  data: StockValueReport[];
  loading: boolean;
}>();

const { formatCurrency, formatPercentage } = useCurrency();

const totalCostValue = computed(() =>
  props.data.reduce((s, r) => s + r.stock_value, 0),
);
const totalRetailValue = computed(() =>
  props.data.reduce((s, r) => s + r.retail_value, 0),
);
const margin = computed(() =>
  totalRetailValue.value > 0
    ? ((totalRetailValue.value - totalCostValue.value) /
        totalRetailValue.value) *
      100
    : 0,
);
</script>
