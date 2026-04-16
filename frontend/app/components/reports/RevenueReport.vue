<template>
  <div class="space-y-6">
    <ReportsReportDateFilter v-model:from="dateFrom" v-model:to="dateTo" />

    <!-- Summary Stats -->
    <div class="grid sm:grid-cols-4 gap-4">
      <UiAppStatCard
        label="Total Revenue"
        :value="formatCurrency(totals.revenue)"
      />
      <UiAppStatCard
        label="Total Expenses"
        :value="formatCurrency(totals.expenses)"
      />
      <UiAppStatCard
        label="Profit"
        :value="formatCurrency(totals.profit)"
        :trending="totals.profit >= 0"
        :change="
          totals.revenue > 0
            ? formatPercentage((totals.profit / totals.revenue) * 100) +
              ' margin'
            : ''
        "
      />
      <UiAppStatCard label="Invoices" :value="totals.invoiceCount" />
    </div>

    <!-- Revenue Table -->
    <UiAppCard title="Revenue by Period" :no-padding="true">
      <div
        v-if="loading"
        class="flex items-center justify-center py-12 text-text-muted text-sm"
      >
        Loading…
      </div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-border bg-surface-alt">
            <th
              class="text-left px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Period
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Revenue
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Expenses
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Profit
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Invoices
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Payments
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in data"
            :key="row.period"
            class="border-b border-border-light last:border-0 hover:bg-surface-alt/60 transition-colors"
          >
            <td class="px-6 py-3 font-medium text-text">{{ row.period }}</td>
            <td
              class="px-6 py-3 text-right tabular-nums text-success font-medium"
            >
              {{ formatCurrency(row.revenue) }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums text-danger">
              {{ formatCurrency(row.expenses) }}
            </td>
            <td
              class="px-6 py-3 text-right tabular-nums font-semibold"
              :class="row.profit >= 0 ? 'text-success' : 'text-danger'"
            >
              {{ formatCurrency(row.profit) }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums">
              {{ row.invoice_count }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums">
              {{ row.payment_count }}
            </td>
          </tr>
        </tbody>
      </table>
    </UiAppCard>
  </div>
</template>

<script setup lang="ts">
import type { RevenueReport } from "~/types";

const props = defineProps<{
  data: RevenueReport[];
  loading: boolean;
}>();

const dateFrom = defineModel<string>("from", { required: true });
const dateTo = defineModel<string>("to", { required: true });

const { formatCurrency, formatPercentage } = useCurrency();

const totals = computed(() => {
  const data = props.data ?? [];
  return {
    revenue: data.reduce((s, r) => s + r.revenue, 0),
    expenses: data.reduce((s, r) => s + r.expenses, 0),
    profit: data.reduce((s, r) => s + r.profit, 0),
    invoiceCount: data.reduce((s, r) => s + r.invoice_count, 0),
  };
});
</script>
