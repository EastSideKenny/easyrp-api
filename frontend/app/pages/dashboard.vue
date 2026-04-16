<template>
  <div class="space-y-8">
    <!-- Greeting -->
    <div>
      <h2 class="text-2xl font-bold text-text tracking-tight">
        {{ greeting }}, {{ user?.name?.split(" ")[0] ?? "there" }}
      </h2>
      <p class="mt-1 text-sm text-text-muted">
        Here's what's happening with your business today.
      </p>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <UiAppStatCard
        label="Revenue (30d)"
        :value="stats ? formatCurrency(stats.revenue_30d) : '—'"
        :change="stats?.revenueChange ? `${stats.revenueChange}%` : undefined"
        :trending="(stats?.revenueChange ?? 0) >= 0"
        subtitle="vs previous 30 days"
      />
      <UiAppStatCard
        label="Invoices"
        :value="stats?.invoices_this_month?.toString() ?? '—'"
        subtitle="this month"
      />
      <UiAppStatCard
        label="Unpaid"
        :value="stats ? formatCurrency(stats.unpaid_total) : '—'"
        :trending="false"
        subtitle="outstanding"
      />
      <UiAppStatCard
        label="New Customers"
        :value="stats?.new_customers?.toString() ?? '—'"
        :trending="true"
        subtitle="this month"
      />
    </div>

    <!-- Recent Invoices -->
    <div>
      <UiAppSectionHeader title="Recent Invoices">
        <NuxtLink
          to="/invoices"
          class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-primary-dark transition-colors"
        >
          View All <ArrowRight class="w-3.5 h-3.5" />
        </NuxtLink>
      </UiAppSectionHeader>

      <UiAppCard class="mt-4" :no-padding="true">
        <!-- Loading skeleton -->
        <div v-if="invoicesLoading" class="divide-y divide-border-light">
          <div
            v-for="i in 4"
            :key="i"
            class="px-6 py-4 flex items-center gap-4"
          >
            <div class="h-4 w-20 bg-surface-alt rounded animate-pulse" />
            <div class="h-4 w-32 bg-surface-alt rounded animate-pulse" />
            <div class="flex-1" />
            <div class="h-4 w-20 bg-surface-alt rounded animate-pulse" />
            <div class="h-5 w-16 bg-surface-alt rounded-md animate-pulse" />
          </div>
        </div>

        <!-- Empty state -->
        <div
          v-else-if="recentInvoices.length === 0"
          class="px-6 py-12 text-center"
        >
          <div
            class="w-12 h-12 rounded-xl bg-surface-alt border border-border flex items-center justify-center mx-auto mb-3"
          >
            <FileText class="w-5 h-5 text-text-muted" />
          </div>
          <p class="text-sm font-medium text-text">No invoices yet</p>
          <p class="text-xs text-text-muted mt-1">
            Create your first invoice to get started.
          </p>
          <UiAppButton size="sm" class="mt-4" to="/invoices/new">
            Create Invoice
          </UiAppButton>
        </div>

        <!-- Table -->
        <table v-else class="w-full text-sm">
          <thead>
            <tr class="border-b border-border bg-surface-alt">
              <th
                class="text-left px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider"
              >
                Invoice
              </th>
              <th
                class="text-left px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider"
              >
                Customer
              </th>
              <th
                class="text-left px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider hidden sm:table-cell"
              >
                Date
              </th>
              <th
                class="text-right px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider"
              >
                Amount
              </th>
              <th
                class="text-right px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider"
              >
                Status
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-light">
            <tr
              v-for="inv in recentInvoices"
              :key="inv.id"
              class="hover:bg-surface-alt/60 transition-colors cursor-pointer"
              @click="navigateTo(`/invoices/${inv.id}`)"
            >
              <td class="px-6 py-4 font-semibold text-primary">
                {{ inv.invoice_number }}
              </td>
              <td class="px-6 py-4 text-text">
                {{ inv.customer ?? "—" }}
              </td>
              <td class="px-6 py-4 text-text-muted hidden sm:table-cell">
                {{ inv.date }}
              </td>
              <td class="px-6 py-4 text-right font-medium tabular-nums">
                {{ formatCurrency(inv.amount) }}
              </td>
              <td class="px-6 py-4 text-right">
                <UiAppBadge :variant="statusVariant(inv.status)">
                  {{ inv.status }}
                </UiAppBadge>
              </td>
            </tr>
          </tbody>
        </table>
      </UiAppCard>
    </div>

    <!-- Quick Actions -->
    <div>
      <UiAppSectionHeader title="Quick Actions" />
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
        <NuxtLink
          v-for="action in quickActions"
          :key="action.to"
          :to="action.to"
          class="bg-surface border border-border rounded-xl p-5 text-center hover:shadow-elevated hover:border-primary/20 transition-all duration-300 group"
        >
          <div
            class="w-10 h-10 rounded-xl bg-primary/8 flex items-center justify-center mx-auto mb-3 group-hover:bg-primary/15 transition-colors"
          >
            <component :is="action.icon" class="w-5 h-5 text-primary" />
          </div>
          <p
            class="text-sm font-semibold text-text group-hover:text-primary transition-colors"
          >
            {{ action.label }}
          </p>
        </NuxtLink>
      </div>
    </div>

    <!-- Low Stock Alert -->
    <div v-if="lowStockProducts.length > 0">
      <UiAppSectionHeader title="Low Stock Alert">
        <NuxtLink
          to="/inventory"
          class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-primary-dark transition-colors"
        >
          Manage Stock <ArrowRight class="w-3.5 h-3.5" />
        </NuxtLink>
      </UiAppSectionHeader>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        <UiAppCard
          v-for="product in lowStockProducts"
          :key="product.id"
          class="flex items-center gap-4 p-4!"
        >
          <div
            class="w-10 h-10 rounded-xl bg-warning/10 text-warning flex items-center justify-center shrink-0"
          >
            <AlertTriangle class="w-5 h-5" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-text truncate">
              {{ product.name }}
            </p>
            <p class="text-xs text-text-muted">
              {{ product.stock_quantity }} left
              <span v-if="product.sku" class="ml-1 text-text-muted/60"
                >· {{ product.sku }}</span
              >
            </p>
          </div>
          <NuxtLink
            :to="`/products/${product.id}`"
            class="text-xs font-medium text-primary hover:text-primary-dark transition-colors shrink-0"
          >
            View →
          </NuxtLink>
        </UiAppCard>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Product } from "~/types";
import {
  Package,
  FileText,
  UserPlus,
  BarChart2,
  AlertTriangle,
  ArrowRight,
} from "lucide-vue-next";

definePageMeta({
  layout: "default",
  middleware: ["auth"],
});

const { user } = useAuth();
const api = useApi();
const { tenant } = useTenant();
const { formatCurrency } = useCurrency();

// ─── Dashboard stats ───

interface DashboardSummary {
  revenue_30d: number;
  revenue_prev_30d: number;
  invoices_this_month: number;
  unpaid_total: number;
  new_customers: number;
  revenueChange?: number;
}

interface DashboardInvoice {
  id: number;
  invoice_number: string;
  customer: string;
  date: string;
  amount: number;
  status: string;
}

interface DashboardResponse {
  summary: DashboardSummary;
  recent_invoices: DashboardInvoice[];
  low_stock_alerts: Product[];
}

const stats = ref<DashboardSummary | null>(null);

// ─── Recent invoices ───

const recentInvoices = ref<DashboardInvoice[]>([]);
const invoicesLoading = ref(true);

// ─── Low stock products ───

const lowStockProducts = ref<Product[]>([]);

// ─── Quick actions ───

const allQuickActions = [
  { icon: Package, label: "New Product", to: "/products/new" },
  { icon: FileText, label: "New Invoice", to: "/invoices/new" },
  { icon: UserPlus, label: "New Customer", to: "/customers/new" },
  {
    icon: BarChart2,
    label: "Reports",
    to: "/reports/revenue",
    module: "reports",
  },
];

const quickActions = computed(() => {
  const modules = tenant.value?.modules;
  if (!modules || modules.length === 0) return allQuickActions;
  return allQuickActions.filter((a) => !a.module || modules.includes(a.module));
});

// ─── Greeting based on time of day ───

const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return "Good morning";
  if (hour < 17) return "Good afternoon";
  return "Good evening";
});

// ─── Helpers ───

function statusVariant(
  status: string,
): "success" | "warning" | "danger" | "neutral" {
  const map: Record<string, "success" | "warning" | "danger" | "neutral"> = {
    paid: "success",
    sent: "warning",
    pending: "warning",
    draft: "neutral",
    overdue: "danger",
    cancelled: "danger",
  };
  return map[status] ?? "neutral";
}

// ─── Data fetching ───

async function loadDashboard() {
  invoicesLoading.value = true;

  try {
    const res = await api<DashboardResponse>("/api/dashboard");

    // Map summary
    const s = res.summary;
    if (s.revenue_prev_30d > 0) {
      s.revenueChange = Math.round(
        ((s.revenue_30d - s.revenue_prev_30d) / s.revenue_prev_30d) * 100,
      );
    }
    stats.value = s;

    // Recent invoices (show only the 5 most recent)
    recentInvoices.value = (res.recent_invoices ?? []).slice(0, 5);

    // Low stock alerts
    lowStockProducts.value = res.low_stock_alerts ?? [];
  } catch (e) {
    console.error("[Dashboard] Failed to load:", e);
  } finally {
    invoicesLoading.value = false;
  }
}

onMounted(() => {
  loadDashboard();
});
</script>
