<template>
  <NuxtLayout name="default" page-title="Reports">
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold tracking-tight text-text">Reports & Analytics</h2>
        <p class="mt-1 text-sm text-text-muted">
          Explore revenue trends, product sales performance, and inventory value.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink
          v-for="card in reportCards"
          :key="card.to"
          :to="card.to"
          class="rounded-xl border border-border bg-surface p-5 hover:shadow-card hover:border-primary/20 transition-all"
        >
          <div class="w-10 h-10 rounded-xl bg-primary/8 text-primary flex items-center justify-center mb-4">
            <component :is="card.icon" class="w-5 h-5" />
          </div>
          <h3 class="text-sm font-semibold text-text">{{ card.title }}</h3>
          <p class="mt-1 text-sm text-text-secondary leading-relaxed">{{ card.description }}</p>
          <p class="mt-3 text-xs font-semibold text-primary">Open report →</p>
        </NuxtLink>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { TrendingUp, BarChart2, Tag } from "lucide-vue-next";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "reports",
});

const reportCards = [
  {
    title: "Revenue",
    description: "Track revenue, costs, profit, and invoice/payment volume over time.",
    to: "/reports/revenue",
    icon: TrendingUp,
  },
  {
    title: "Sales by Product",
    description: "See top products by units sold, revenue contribution, and average price.",
    to: "/reports/sales",
    icon: BarChart2,
  },
  {
    title: "Stock Value",
    description: "Monitor current inventory value at cost and retail pricing.",
    to: "/reports/stock-value",
    icon: Tag,
  },
];
</script>
