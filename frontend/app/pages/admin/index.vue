<template>
    <NuxtLayout name="admin">
        <div class="space-y-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-text">
                    Admin Overview
                </h1>
                <p class="mt-1 text-sm text-text-secondary">
                    Site-wide statistics and plan distribution.
                </p>
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

            <template v-else-if="stats">
                <!-- Stat cards -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <div class="bg-surface rounded-xl border border-border p-5">
                        <p class="text-sm font-medium text-text-secondary">
                            Total Tenants
                        </p>
                        <p class="mt-1 text-3xl font-bold text-text">
                            {{ stats.total_tenants }}
                        </p>
                    </div>
                    <div class="bg-surface rounded-xl border border-border p-5">
                        <p class="text-sm font-medium text-text-secondary">
                            Active Tenants
                        </p>
                        <p class="mt-1 text-3xl font-bold text-success">
                            {{ stats.active_tenants }}
                        </p>
                    </div>
                    <div class="bg-surface rounded-xl border border-border p-5">
                        <p class="text-sm font-medium text-text-secondary">
                            Total Subscriptions
                        </p>
                        <p class="mt-1 text-3xl font-bold text-text">
                            {{ stats.total_subscriptions }}
                        </p>
                    </div>
                    <div class="bg-surface rounded-xl border border-border p-5">
                        <p class="text-sm font-medium text-text-secondary">
                            Active Subscriptions
                        </p>
                        <p class="mt-1 text-3xl font-bold text-success">
                            {{ stats.active_subscriptions }}
                        </p>
                    </div>
                </div>

                <!-- Plan distribution -->
                <div
                    class="bg-surface rounded-xl border border-border overflow-hidden"
                >
                    <div class="px-5 py-4 border-b border-border">
                        <h2 class="text-base font-semibold text-text">
                            Plan distribution
                        </h2>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-border text-left text-text-secondary"
                            >
                                <th class="px-5 py-3 font-medium">Plan</th>
                                <th class="px-5 py-3 font-medium">Slug</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium text-right">
                                    Tenants
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="plan in stats.plans"
                                :key="plan.id"
                                class="hover:bg-surface-alt transition-colors"
                            >
                                <td class="px-5 py-3 font-medium text-text">
                                    {{ plan.name }}
                                </td>
                                <td
                                    class="px-5 py-3 text-text-secondary font-mono text-xs"
                                >
                                    {{ plan.slug }}
                                </td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="
                                            plan.is_active
                                                ? 'bg-success/10 text-success'
                                                : 'bg-text-muted/10 text-text-muted'
                                        "
                                    >
                                        {{
                                            plan.is_active ? "Active" : "Hidden"
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-5 py-3 text-right font-semibold text-text"
                                >
                                    {{ plan.tenants_count }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({ layout: false, middleware: ["admin"] });

const { fetchStats } = useAdmin();
const loading = ref(true);
const stats = ref<Awaited<ReturnType<typeof fetchStats>> | null>(null);

onMounted(async () => {
    try {
        stats.value = await fetchStats();
    } finally {
        loading.value = false;
    }
});
</script>
