<template>
    <div class="max-w-3xl mx-auto space-y-8">
        <UiAppSectionHeader
            title="Plan & Billing"
            description="View your current plan, trial status, and feature usage."
        />

        <!-- Loading -->
        <UiAppCard v-if="subscriptionLoading" :no-padding="true">
            <div class="flex items-center justify-center py-16 text-text-muted">
                <svg
                    class="w-5 h-5 animate-spin mr-2"
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
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                    />
                </svg>
                Loading…
            </div>
        </UiAppCard>

        <template v-else-if="subscription">
            <!-- Current Plan -->
            <UiAppCard title="Current Plan">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-semibold text-text">
                            {{ subscription.plan?.name ?? "Unknown Plan" }}
                        </p>
                        <p class="text-sm text-text-muted mt-1">
                            Status:
                            <UiAppBadge :variant="statusBadge">{{
                                statusLabel
                            }}</UiAppBadge>
                        </p>
                    </div>
                    <div v-if="subscription.plan" class="text-right">
                        <p class="text-2xl font-bold text-text">
                            ${{ subscription.plan.price_monthly }}
                            <span class="text-sm font-normal text-text-muted"
                                >/mo</span
                            >
                        </p>
                    </div>
                </div>
            </UiAppCard>

            <!-- Trial Info -->
            <UiAppCard v-if="isTrialing" title="Trial Period">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-text-secondary"
                            >Days remaining</span
                        >
                        <span
                            class="text-sm font-semibold"
                            :class="isUrgent ? 'text-danger' : 'text-text'"
                        >
                            {{ daysLeft }} {{ daysLeft === 1 ? "day" : "days" }}
                        </span>
                    </div>
                    <div class="w-full bg-surface-alt rounded-full h-2">
                        <div
                            class="h-2 rounded-full transition-all duration-300"
                            :class="isUrgent ? 'bg-danger' : 'bg-primary'"
                            :style="{
                                width: `${Math.max(5, 100 - (daysLeft / 14) * 100)}%`,
                            }"
                        />
                    </div>
                    <p
                        v-if="subscription.trial_ends_at"
                        class="text-xs text-text-muted"
                    >
                        Trial ends on
                        {{
                            new Date(
                                subscription.trial_ends_at,
                            ).toLocaleDateString()
                        }}
                    </p>
                </div>
            </UiAppCard>

            <!-- Expired Notice -->
            <UiAppCard v-if="isExpired">
                <div class="flex items-center gap-3 text-danger">
                    <AlertCircle class="w-5 h-5 shrink-0" />
                    <div>
                        <p class="font-semibold">Your trial has expired</p>
                        <p class="text-sm text-text-muted mt-0.5">
                            Contact the site administrator to upgrade your plan.
                        </p>
                    </div>
                </div>
            </UiAppCard>

            <!-- Usage -->
            <UiAppCard v-if="usageList.length" title="Feature Usage">
                <div class="space-y-4">
                    <UiAppUsageMeter
                        v-for="u in usageList"
                        :key="u.feature"
                        :usage="u"
                    />
                </div>
            </UiAppCard>
        </template>

        <!-- No subscription -->
        <UiAppCard v-else>
            <div class="text-center py-8 text-text-muted">
                <p class="text-lg font-medium text-text mb-1">
                    No active subscription
                </p>
                <p class="text-sm">
                    Contact the site administrator to get started with a plan.
                </p>
            </div>
        </UiAppCard>
    </div>
</template>

<script setup lang="ts">
import { AlertCircle } from "lucide-vue-next";
import type { FeatureUsage } from "~/types";

definePageMeta({
    layout: "default",
    middleware: ["auth", "tenant-admin"],
});

const {
    subscription,
    subscriptionLoading,
    isTrialing,
    isExpired,
    isUrgent,
    daysLeft,
} = useSubscription();

const statusLabel = computed(() => {
    const s = subscription.value?.status;
    if (s === "trialing") return "Trial";
    if (s === "active") return "Active";
    if (s === "expired") return "Expired";
    if (s === "canceled") return "Canceled";
    if (s === "past_due") return "Past Due";
    return s ?? "Unknown";
});

const statusBadge = computed(() => {
    const s = subscription.value?.status;
    if (s === "active") return "success" as const;
    if (s === "trialing") return "warning" as const;
    if (s === "expired" || s === "canceled") return "danger" as const;
    return "neutral" as const;
});

const usageList = computed<FeatureUsage[]>(() => {
    const raw = subscription.value?.usage;
    if (!raw) return [];
    if (Array.isArray(raw)) return raw;
    return Object.entries(raw).map(([feature, data]) => ({ feature, ...data }));
});
</script>
