<template>
    <NuxtLayout name="admin">
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-text">
                    Tenants
                </h1>
                <p class="mt-1 text-sm text-text-secondary">
                    Manage all registered tenants and their plans.
                </p>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or subdomain…"
                    class="flex-1 px-3 py-2 bg-surface border border-border rounded-lg text-sm text-text placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    @input="debouncedFetch"
                />
                <select
                    v-model="statusFilter"
                    class="px-3 py-2 bg-surface border border-border rounded-lg text-sm text-text focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    @change="loadTenants"
                >
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- Loading -->
            <div
                v-if="loading"
                class="flex items-center justify-center py-20 text-text-muted"
            >
                <svg
                    class="w-6 h-6 animate-spin"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    />
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    />
                </svg>
            </div>

            <template v-else-if="tenantData">
                <!-- Table -->
                <div
                    class="bg-surface rounded-xl border border-border overflow-hidden"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b border-border text-left text-text-secondary"
                                >
                                    <th class="px-5 py-3 font-medium">
                                        Tenant
                                    </th>
                                    <th class="px-5 py-3 font-medium">
                                        Subdomain
                                    </th>
                                    <th class="px-5 py-3 font-medium">Plan</th>
                                    <th class="px-5 py-3 font-medium">
                                        Subscription
                                    </th>
                                    <th class="px-5 py-3 font-medium">Users</th>
                                    <th class="px-5 py-3 font-medium">
                                        Status
                                    </th>
                                    <th class="px-5 py-3 font-medium">
                                        Created
                                    </th>
                                    <th
                                        class="px-5 py-3 font-medium text-right"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="tenant in tenantData.data"
                                    :key="tenant.id"
                                    class="hover:bg-surface-alt transition-colors"
                                >
                                    <td class="px-5 py-3 font-medium text-text">
                                        {{ tenant.name }}
                                    </td>
                                    <td
                                        class="px-5 py-3 text-text-secondary font-mono text-xs"
                                    >
                                        {{ tenant.subdomain }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span
                                            v-if="tenant.plan"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary"
                                        >
                                            {{ tenant.plan.name }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-text-muted text-xs"
                                            >No plan</span
                                        >
                                    </td>
                                    <td class="px-5 py-3">
                                        <span
                                            v-if="tenant.subscriptions?.length"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="
                                                subscriptionBadgeClass(
                                                    tenant.subscriptions[0]
                                                        .status,
                                                )
                                            "
                                        >
                                            {{ tenant.subscriptions[0].status }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-text-muted text-xs"
                                            >None</span
                                        >
                                    </td>
                                    <td class="px-5 py-3 text-text-secondary">
                                        {{ tenant.users_count }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="
                                                tenant.is_active
                                                    ? 'bg-success/10 text-success'
                                                    : 'bg-danger/10 text-danger'
                                            "
                                        >
                                            {{
                                                tenant.is_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-5 py-3 text-text-secondary text-xs"
                                    >
                                        {{ formatDate(tenant.created_at) }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div
                                            class="flex items-center justify-end gap-2"
                                        >
                                            <button
                                                class="text-xs px-2.5 py-1.5 rounded-lg border border-border text-text-secondary hover:text-text hover:bg-surface-alt transition-colors"
                                                @click="openPlanModal(tenant)"
                                            >
                                                Change plan
                                            </button>
                                            <button
                                                class="text-xs px-2.5 py-1.5 rounded-lg border transition-colors"
                                                :class="
                                                    tenant.is_active
                                                        ? 'border-danger/30 text-danger hover:bg-danger/5'
                                                        : 'border-success/30 text-success hover:bg-success/5'
                                                "
                                                @click="
                                                    handleToggleStatus(tenant)
                                                "
                                            >
                                                {{
                                                    tenant.is_active
                                                        ? "Deactivate"
                                                        : "Activate"
                                                }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="tenantData.data.length === 0"
                        class="px-5 py-12 text-center text-text-muted text-sm"
                    >
                        No tenants found.
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="tenantData.last_page > 1"
                    class="flex items-center justify-between"
                >
                    <p class="text-sm text-text-secondary">
                        Showing page {{ tenantData.current_page }} of
                        {{ tenantData.last_page }} ({{ tenantData.total }}
                        total)
                    </p>
                    <div class="flex gap-2">
                        <button
                            :disabled="tenantData.current_page <= 1"
                            class="px-3 py-1.5 text-sm border border-border rounded-lg hover:bg-surface-alt disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            @click="goToPage(tenantData!.current_page - 1)"
                        >
                            Previous
                        </button>
                        <button
                            :disabled="
                                tenantData.current_page >= tenantData.last_page
                            "
                            class="px-3 py-1.5 text-sm border border-border rounded-lg hover:bg-surface-alt disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            @click="goToPage(tenantData!.current_page + 1)"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Plan change modal -->
        <Teleport to="body">
            <div
                v-if="showPlanModal"
                class="fixed inset-0 z-50 flex items-center justify-center"
            >
                <div
                    class="absolute inset-0 bg-black/40"
                    @click="showPlanModal = false"
                />
                <div
                    class="relative bg-surface rounded-xl border border-border shadow-xl max-w-md w-full mx-4 p-6 space-y-4"
                >
                    <h2 class="text-lg font-semibold text-text">
                        Change plan for {{ selectedTenant?.name }}
                    </h2>
                    <p class="text-sm text-text-secondary">
                        Current plan:
                        <strong>{{
                            selectedTenant?.plan?.name || "None"
                        }}</strong>
                    </p>

                    <div class="space-y-2">
                        <label
                            v-for="plan in availablePlans"
                            :key="plan.id"
                            class="flex items-center gap-3 p-3 rounded-lg border transition-colors cursor-pointer"
                            :class="
                                selectedPlanId === plan.id
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:bg-surface-alt'
                            "
                        >
                            <input
                                v-model="selectedPlanId"
                                type="radio"
                                :value="plan.id"
                                class="accent-primary"
                            />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-text">
                                    {{ plan.name }}
                                    <span
                                        v-if="!plan.is_active"
                                        class="text-xs text-text-muted"
                                        >(hidden)</span
                                    >
                                </p>
                                <p class="text-xs text-text-secondary">
                                    €{{ plan.price_monthly }}/mo · €{{
                                        plan.price_yearly
                                    }}/yr
                                </p>
                            </div>
                            <span class="text-xs text-text-muted">
                                {{ plan.tenants_count }} tenants
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            class="px-4 py-2 text-sm border border-border rounded-lg hover:bg-surface-alt transition-colors"
                            @click="showPlanModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            :disabled="
                                !selectedPlanId ||
                                selectedPlanId === selectedTenant?.plan?.id ||
                                savingPlan
                            "
                            class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            @click="handlePlanChange"
                        >
                            {{ savingPlan ? "Saving…" : "Update plan" }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </NuxtLayout>
</template>

<script setup lang="ts">
import type { Plan } from "~/types";

definePageMeta({ layout: false, middleware: ["admin"] });

const { fetchTenants, fetchPlans, updateTenantPlan, toggleTenantStatus } =
    useAdmin();

const loading = ref(true);
const search = ref("");
const statusFilter = ref("");
const tenantData = ref<Awaited<ReturnType<typeof fetchTenants>> | null>(null);

// Plan modal
const showPlanModal = ref(false);
const selectedTenant = ref<any>(null);
const selectedPlanId = ref<number | null>(null);
const availablePlans = ref<(Plan & { tenants_count: number })[]>([]);
const savingPlan = ref(false);

let debounceTimer: ReturnType<typeof setTimeout>;
function debouncedFetch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => loadTenants(), 300);
}

async function loadTenants(page = 1) {
    loading.value = true;
    try {
        tenantData.value = await fetchTenants({
            page,
            search: search.value || undefined,
            status: statusFilter.value || undefined,
        });
    } finally {
        loading.value = false;
    }
}

function goToPage(page: number) {
    loadTenants(page);
}

async function openPlanModal(tenant: any) {
    selectedTenant.value = tenant;
    selectedPlanId.value = tenant.plan?.id ?? null;
    showPlanModal.value = true;

    if (availablePlans.value.length === 0) {
        availablePlans.value = await fetchPlans();
    }
}

async function handlePlanChange() {
    if (!selectedTenant.value || !selectedPlanId.value) return;
    savingPlan.value = true;
    try {
        await updateTenantPlan(selectedTenant.value.id, selectedPlanId.value);
        showPlanModal.value = false;
        await loadTenants(tenantData.value?.current_page ?? 1);
    } finally {
        savingPlan.value = false;
    }
}

async function handleToggleStatus(tenant: any) {
    const action = tenant.is_active ? "deactivate" : "activate";
    if (!confirm(`Are you sure you want to ${action} "${tenant.name}"?`))
        return;

    await toggleTenantStatus(tenant.id);
    await loadTenants(tenantData.value?.current_page ?? 1);
}

function subscriptionBadgeClass(status: string) {
    switch (status) {
        case "active":
            return "bg-success/10 text-success";
        case "trialing":
            return "bg-warning/10 text-warning";
        case "expired":
        case "canceled":
            return "bg-danger/10 text-danger";
        default:
            return "bg-text-muted/10 text-text-muted";
    }
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

onMounted(() => loadTenants());
</script>
