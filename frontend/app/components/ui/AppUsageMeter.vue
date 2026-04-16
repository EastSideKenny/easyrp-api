<template>
  <div v-if="usage" class="space-y-1.5">
    <!-- Label row -->
    <div class="flex items-center justify-between text-xs">
      <span class="text-text-muted capitalize">{{
        label || usage.feature
      }}</span>
      <span :class="textClass">
        <template v-if="isUnlimited">Unlimited</template>
        <template v-else-if="isAtCap">
          Limit reached —
          <NuxtLink
            to="/settings/billing"
            class="font-semibold underline underline-offset-2"
          >
            upgrade
          </NuxtLink>
        </template>
        <template v-else-if="isWarning">
          {{ usage.used }} of {{ usage.limit }} used — approaching limit
        </template>
        <template v-else> {{ usage.used }} of {{ usage.limit }} used </template>
      </span>
    </div>

    <!-- Progress bar (hidden when unlimited) -->
    <div
      v-if="!isUnlimited"
      class="h-1.5 w-full rounded-full bg-border overflow-hidden"
    >
      <div
        class="h-full rounded-full transition-all duration-500"
        :class="barClass"
        :style="{ width: `${percent}%` }"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { FeatureUsage } from "~/types";

const props = defineProps<{
  /** The FeatureUsage object from useSubscription().usageFor(feature) */
  usage: FeatureUsage | undefined;
  /** Optional display label (defaults to feature code) */
  label?: string;
}>();

const isUnlimited = computed(() => !props.usage || props.usage.limit === null);

const percent = computed(() => {
  if (!props.usage || props.usage.limit === null || props.usage.limit === 0)
    return 0;
  return Math.min(
    100,
    Math.round((props.usage.used / props.usage.limit) * 100),
  );
});

const isAtCap = computed(
  () => !isUnlimited.value && (props.usage?.remaining ?? 1) <= 0,
);
const isWarning = computed(() => !isAtCap.value && percent.value >= 80);

const barClass = computed(() => {
  if (isAtCap.value) return "bg-danger";
  if (isWarning.value) return "bg-warning";
  return "bg-primary/60";
});

const textClass = computed(() => {
  if (isAtCap.value) return "text-danger font-medium";
  if (isWarning.value) return "text-warning font-medium";
  return "text-text-muted";
});
</script>
