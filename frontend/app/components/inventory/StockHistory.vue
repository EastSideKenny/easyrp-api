<template>
  <UiAppCard title="Stock Movement History" :no-padding="true">
    <div
      v-if="loading"
      class="flex items-center justify-center py-12 text-text-muted"
    >
      <svg class="w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
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

    <div
      v-else-if="!movements?.length"
      class="py-12 text-center text-sm text-text-muted"
    >
      No movements recorded.
    </div>

    <div v-else class="divide-y divide-border-light">
      <div
        v-for="mov in movements"
        :key="mov.id"
        class="px-6 py-4 flex items-center justify-between gap-4"
      >
        <div class="flex items-center gap-4 min-w-0">
          <div
            class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
            :class="typeIconClass(mov.type)"
          >
            {{ typeIcon(mov.type) }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-text truncate">
              {{
                mov.product?.name ??
                mov.product_name ??
                `Product #${mov.product_id}`
              }}
            </p>
            <p class="text-xs text-text-muted truncate capitalize">
              {{ mov.type.replace("_", " ") }}
            </p>
          </div>
        </div>

        <div class="text-right shrink-0">
          <p
            class="text-sm font-semibold tabular-nums"
            :class="displayQty(mov) < 0 ? 'text-danger' : 'text-success'"
          >
            {{ displayQty(mov) > 0 ? "+" : "" }}{{ displayQty(mov) }}
          </p>
          <p v-if="mov.reference_id" class="text-xs text-text-muted">
            Ref: {{ mov.reference_id }}
          </p>
          <p class="text-[11px] text-text-muted">
            {{ new Date(mov.created_at).toLocaleString() }}
          </p>
        </div>
      </div>
    </div>
  </UiAppCard>
</template>

<script setup lang="ts">
import type { StockMovement, StockMovementType } from "~/types";

defineProps<{
  movements: StockMovement[];
  loading: boolean;
}>();

function displayQty(mov: StockMovement): number {
  // Sales should always display as negative, even if the DB stores a positive value
  if (mov.type === "sale") return -Math.abs(mov.quantity_change);
  return mov.quantity_change;
}

function typeIcon(type: StockMovementType): string {
  const map: Record<StockMovementType, string> = {
    sale: "−",
    manual_adjustment: "±",
  };
  return map[type];
}

function typeIconClass(type: StockMovementType): string {
  const map: Record<StockMovementType, string> = {
    sale: "bg-danger/10 text-danger",
    manual_adjustment: "bg-info/10 text-info",
  };
  return map[type];
}
</script>
