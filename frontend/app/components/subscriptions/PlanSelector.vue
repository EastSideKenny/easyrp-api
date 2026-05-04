<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <span class="text-sm font-medium text-text">Billing</span>
            <div
                class="inline-flex rounded-xl border border-border bg-surface-alt p-0.5 text-xs font-semibold"
            >
                <button
                    type="button"
                    class="rounded-[10px] px-3 py-1.5 transition-colors"
                    :class="
                        billingCycle === 'monthly'
                            ? 'bg-surface text-text shadow-sm'
                            : 'text-text-muted hover:text-text'
                    "
                    @click="$emit('update:billingCycle', 'monthly')"
                >
                    Monthly
                </button>
                <button
                    type="button"
                    class="rounded-[10px] px-3 py-1.5 transition-colors"
                    :class="
                        billingCycle === 'yearly'
                            ? 'bg-surface text-text shadow-sm'
                            : 'text-text-muted hover:text-text'
                    "
                    @click="$emit('update:billingCycle', 'yearly')"
                >
                    Yearly
                </button>
            </div>
        </div>

        <div
            v-if="plansLoading"
            class="text-sm text-text-muted py-6 text-center"
        >
            Loading plans…
        </div>

        <div v-else class="space-y-2">
            <button
                v-for="plan in plans"
                :key="plan.id"
                type="button"
                class="w-full border rounded-xl px-4 py-3 text-left transition-all duration-200"
                :class="buttonClass(plan.id)"
                @click="$emit('update:modelValue', plan.id)"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-bold text-text">{{
                                plan.name
                            }}</span>
                            <UiAppBadge
                                v-if="plan.id === currentPlanId"
                                variant="neutral"
                                :dot="false"
                                class="text-[10px]"
                            >
                                Current
                            </UiAppBadge>
                            <UiAppBadge
                                v-if="plan.is_free"
                                variant="success"
                                :dot="false"
                                class="text-[10px]"
                            >
                                No card
                            </UiAppBadge>
                        </div>
                        <p
                            v-if="plan.features?.length"
                            class="text-xs text-text-muted mt-1 line-clamp-2"
                        >
                            {{ featurePreview(plan) }}
                        </p>
                    </div>
                    <div
                        class="text-right shrink-0 text-sm font-bold text-text"
                    >
                        <template v-if="plan.is_free">Free</template>
                        <template v-else>
                            {{ formatMoney(plan) }}
                            <span class="text-xs font-normal text-text-muted">{{
                                billingCycle === "yearly" ? "/yr" : "/mo"
                            }}</span>
                        </template>
                    </div>
                </div>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Plan } from "~/types";

const props = defineProps<{
    plans: Plan[];
    modelValue: number | null;
    billingCycle: "monthly" | "yearly";
    currentPlanId?: number | null;
    plansLoading?: boolean;
}>();

defineEmits<{
    "update:modelValue": [id: number];
    "update:billingCycle": [cycle: "monthly" | "yearly"];
}>();

const { formatCurrency } = useCurrency();

function formatMoney(plan: Plan): string {
    const cur = plan.currency ?? "USD";
    const n =
        props.billingCycle === "yearly"
            ? plan.price_yearly
            : plan.price_monthly;
    return formatCurrency(n, cur);
}

function featurePreview(plan: Plan): string {
    const list = plan.features ?? [];
    return list
        .slice(0, 4)
        .map((f) => f.name)
        .join(" · ");
}

function buttonClass(planId: number): string {
    const selected = props.modelValue === planId;
    return selected
        ? "border-primary bg-primary/5 ring-2 ring-primary/20"
        : "border-border bg-surface hover:border-primary/30 hover:bg-surface-alt";
}
</script>
