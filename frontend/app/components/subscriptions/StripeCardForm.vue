<template>
  <div class="space-y-3">
    <UiAppFormField label="Card" id="stripe-card-element">
      <div
        v-if="!stripePublicKey?.trim()"
        class="rounded-xl border border-border bg-surface-alt px-4 py-3 text-sm text-text-muted"
      >
        Card payments are not configured for this environment.
      </div>
      <div v-else>
        <div
          ref="mountEl"
          class="rounded-xl border border-border bg-surface px-3 py-3.5 min-h-[48px] transition-colors focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/15"
          :class="{ 'opacity-50 pointer-events-none': disabled || mounting }"
        />
        <p v-if="mountError" class="text-xs text-danger mt-1.5">{{ mountError }}</p>
      </div>
    </UiAppFormField>
    <p v-if="stripeError" class="text-xs text-danger">{{ stripeError }}</p>
  </div>
</template>

<script setup lang="ts">
import type { StripeCardElement } from "@stripe/stripe-js";

const props = withDefaults(
  defineProps<{
    disabled?: boolean;
  }>(),
  { disabled: false },
);

const config = useRuntimeConfig();
const stripePublicKey = computed(
  () => (config.public.stripePublicKey as string) ?? "",
);

const { initStripe, createPaymentMethod, stripeError } = useStripe();
const mountEl = ref<HTMLElement | null>(null);
const mounting = ref(true);
const mountError = ref<string | null>(null);
let card: StripeCardElement | null = null;

onMounted(async () => {
  if (!stripePublicKey.value?.trim()) {
    mounting.value = false;
    return;
  }
  await nextTick();
  if (!mountEl.value) {
    mountError.value = "Could not mount card field.";
    mounting.value = false;
    return;
  }
  try {
    const stripe = await initStripe();
    const elements = stripe.elements({
      appearance: {
        theme: "stripe",
        variables: {
          colorPrimary: "#4f46e5",
          borderRadius: "12px",
        },
      },
    });
    card = elements.create("card", {
      style: {
        base: {
          fontSize: "16px",
          color: "#0f172a",
          fontFamily: '"Inter Variable", Inter, system-ui, sans-serif',
          "::placeholder": { color: "#94a3b8" },
        },
        invalid: { color: "#ef4444" },
      },
    });
    card.mount(mountEl.value);
    card.on("change", () => {
      mountError.value = null;
    });
  } catch (e: unknown) {
    mountError.value =
      e instanceof Error ? e.message : "Failed to load card field.";
  } finally {
    mounting.value = false;
  }
});

watch(
  () => props.disabled,
  (d) => {
    if (card) card.update({ disabled: d });
  },
);

onUnmounted(() => {
  card?.destroy();
  card = null;
});

async function getPaymentMethodId(billing?: {
  name?: string;
  email?: string;
}): Promise<string | null> {
  if (!stripePublicKey.value?.trim() || !card) return null;
  return createPaymentMethod(card, {
    name: billing?.name,
    email: billing?.email,
  });
}

defineExpose({ getPaymentMethodId });
</script>
