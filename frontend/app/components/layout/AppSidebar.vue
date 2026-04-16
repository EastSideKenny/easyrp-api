<template>
  <aside
    class="fixed inset-y-0 left-0 z-40 w-64 bg-surface border-r border-border flex flex-col transition-transform duration-300 lg:translate-x-0"
    :class="open ? 'translate-x-0' : '-translate-x-full'"
  >
    <!-- Logo -->
    <div
      class="h-16 px-5 flex items-center gap-3 border-b border-border shrink-0"
    >
      <div
        class="w-8 h-8 rounded-xl bg-linear-to-br from-primary to-primary-light flex items-center justify-center shrink-0"
      >
        <Zap class="w-4 h-4 text-white" />
      </div>
      <span class="font-bold text-base tracking-tight text-text">EasyRP</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
      <p
        class="px-3 mb-1.5 mt-1 text-[10px] font-semibold text-text-muted uppercase tracking-widest"
      >
        Core
      </p>
      <NuxtLink
        v-for="item in coreNav"
        :key="item.to"
        :to="item.to"
        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
        :class="
          isActive(item.to)
            ? 'bg-primary/10 text-primary'
            : 'text-text-secondary hover:bg-surface-alt hover:text-text'
        "
        @click="$emit('close')"
      >
        <component
          :is="item.icon"
          class="w-4 h-4 shrink-0"
          :class="isActive(item.to) ? 'text-primary' : 'text-text-muted'"
        />
        <span class="flex-1">{{ item.label }}</span>
      </NuxtLink>

      <template v-if="inventoryNav.length">
        <p
          class="px-3 mb-1.5 mt-5 text-[10px] font-semibold text-text-muted uppercase tracking-widest"
        >
          Inventory
        </p>
        <NuxtLink
          v-for="item in inventoryNav"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
          :class="
            isActive(item.to)
              ? 'bg-primary/10 text-primary'
              : 'text-text-secondary hover:bg-surface-alt hover:text-text'
          "
          @click="$emit('close')"
        >
          <component
            :is="item.icon"
            class="w-4 h-4 shrink-0"
            :class="isActive(item.to) ? 'text-primary' : 'text-text-muted'"
          />
          <span class="flex-1">{{ item.label }}</span>
        </NuxtLink>
      </template>

      <template v-if="storeNav.length">
        <p
          class="px-3 mb-1.5 mt-5 text-[10px] font-semibold text-text-muted uppercase tracking-widest"
        >
          Store
        </p>
        <NuxtLink
          v-for="item in storeNav"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
          :class="
            isActive(item.to)
              ? 'bg-primary/10 text-primary'
              : 'text-text-secondary hover:bg-surface-alt hover:text-text'
          "
          @click="$emit('close')"
        >
          <component
            :is="item.icon"
            class="w-4 h-4 shrink-0"
            :class="isActive(item.to) ? 'text-primary' : 'text-text-muted'"
          />
          <span class="flex-1">{{ item.label }}</span>
          <span
            v-if="item.badge"
            class="text-[10px] font-semibold bg-secondary/10 text-secondary px-2 py-0.5 rounded-md"
            >{{ item.badge }}</span
          >
        </NuxtLink>
      </template>

      <template v-if="insightsNav.length">
        <p
          class="px-3 mb-1.5 mt-5 text-[10px] font-semibold text-text-muted uppercase tracking-widest"
        >
          Insights
        </p>
        <NuxtLink
          v-for="item in insightsNav"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
          :class="
            isActive(item.to)
              ? 'bg-primary/10 text-primary'
              : 'text-text-secondary hover:bg-surface-alt hover:text-text'
          "
          @click="$emit('close')"
        >
          <component
            :is="item.icon"
            class="w-4 h-4 shrink-0"
            :class="isActive(item.to) ? 'text-primary' : 'text-text-muted'"
          />
          <span class="flex-1">{{ item.label }}</span>
        </NuxtLink>
      </template>

      <template v-if="settingsNav.length">
        <p
          class="px-3 mb-1.5 mt-5 text-[10px] font-semibold text-text-muted uppercase tracking-widest"
        >
          Settings
        </p>
        <NuxtLink
          v-for="item in settingsNav"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
          :class="
            isActive(item.to)
              ? 'bg-primary/10 text-primary'
              : 'text-text-secondary hover:bg-surface-alt hover:text-text'
          "
          @click="$emit('close')"
        >
          <component
            :is="item.icon"
            class="w-4 h-4 shrink-0"
            :class="isActive(item.to) ? 'text-primary' : 'text-text-muted'"
          />
          <span class="flex-1">{{ item.label }}</span>
        </NuxtLink>
      </template>
    </nav>

    <!-- User -->
    <div class="px-3 py-3 border-t border-border shrink-0">
      <div
        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-surface-alt transition-colors"
      >
        <div
          class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold shrink-0"
        >
          {{ userInitial }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-text truncate">{{ userName }}</p>
          <p class="text-xs text-text-muted truncate">{{ userEmail }}</p>
        </div>
        <button
          class="p-1.5 rounded-lg text-text-muted hover:text-danger hover:bg-danger/10 transition-colors shrink-0"
          title="Sign out"
          @click="handleLogout"
        >
          <LogOut class="w-4 h-4" />
        </button>
      </div>
    </div>
  </aside>

  <!-- Overlay for mobile -->
  <Transition
    enter-active-class="transition duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open"
      class="fixed inset-0 z-30 bg-text/20 backdrop-blur-sm lg:hidden"
      @click="$emit('close')"
    />
  </Transition>
</template>

<script setup lang="ts">
import type { NavItem } from "~/types";
import {
  LayoutDashboard,
  Package,
  Users,
  FileText,
  FileCheck,
  CreditCard,
  ClipboardList,
  SlidersHorizontal,
  ShoppingCart,
  Store,
  TrendingUp,
  BarChart2,
  Tag,
  Settings,
  Zap,
  LogOut,
} from "lucide-vue-next";

const props = defineProps<{
  open: boolean;
  userName?: string;
  userEmail?: string;
}>();

defineEmits<{
  close: [];
}>();

const route = useRoute();
const { logout } = useAuth();
const { tenant } = useTenant();

const userInitial = computed(() => {
  const name = props.userName;
  return name ? name.charAt(0).toUpperCase() : "U";
});

function isActive(to: string): boolean {
  if (to === "/dashboard" || to === "/inventory" || to === "/settings")
    return route.path === to;
  return route.path.startsWith(to);
}

async function handleLogout() {
  await logout();
  navigateTo("/");
}

/** Returns true if the tenant has the given module enabled (or if modules aren't set). */
function hasModule(module: string): boolean {
  const modules = tenant.value?.modules;
  if (!modules || modules.length === 0) return true;
  return modules.includes(module);
}

const coreNav = computed<NavItem[]>(() => {
  const items: NavItem[] = [
    { label: "Dashboard", icon: LayoutDashboard, to: "/dashboard" },
  ];
  if (hasModule("products"))
    items.push({ label: "Products", icon: Package, to: "/products" });
  items.push({ label: "Customers", icon: Users, to: "/customers" });
  // items.push({ label: "Orders", icon: ShoppingCart, to: "/orders" });
  if (hasModule("invoices"))
    items.push({ label: "Offers", icon: FileCheck, to: "/offers" });
  if (hasModule("invoices"))
    items.push({ label: "Invoices", icon: FileText, to: "/invoices" });
  if (hasModule("invoices"))
    items.push({ label: "Payments", icon: CreditCard, to: "/payments" });
  return items;
});

const inventoryNav = computed<NavItem[]>(() => {
  if (!hasModule("products")) return [];
  return [
    { label: "Stock Overview", icon: ClipboardList, to: "/inventory" },
    {
      label: "Adjustments",
      icon: SlidersHorizontal,
      to: "/inventory/adjustments",
    },
  ];
});

const storeNav = computed<NavItem[]>(() => {
  const items: NavItem[] = [];
  if (hasModule("storefront"))
    items.push({
      label: "Storefront",
      icon: Store,
      to: "/store",
      badge: "Public",
    });
  return items;
});

const insightsNav = computed<NavItem[]>(() => {
  if (!hasModule("reports")) return [];
  return [
    { label: "Revenue", icon: TrendingUp, to: "/reports/revenue" },
    { label: "Sales by Product", icon: BarChart2, to: "/reports/sales" },
    { label: "Stock Value", icon: Tag, to: "/reports/stock-value" },
  ];
});

const settingsNav = computed<NavItem[]>(() => {
  const items: NavItem[] = [];

  items.push({ label: "Settings", icon: Settings, to: "/settings" });
  if (hasModule("storefront"))
    items.push({ label: "Webshop", icon: Settings, to: "/settings/webshop" });
  return items;
});
</script>
