<template>
  <form class="space-y-8" @submit.prevent="$emit('submit')">
    <!-- Customer Info -->
    <div>
      <h3 class="text-lg font-semibold text-text mb-4">Your Details</h3>
      <div class="grid sm:grid-cols-2 gap-4">
        <UiAppFormField label="Full Name" :error="errors.name" required>
          <input
            v-model="customerInfo.name"
            type="text"
            placeholder="John Doe"
            class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
          />
        </UiAppFormField>
        <UiAppFormField label="Email" :error="errors.email" required>
          <input
            v-model="customerInfo.email"
            type="email"
            placeholder="john@example.com"
            class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
          />
        </UiAppFormField>
      </div>
    </div>

    <!-- Order Summary -->
    <div>
      <h3 class="text-lg font-semibold text-text mb-4">Order Summary</h3>
      <UiAppCard :no-padding="false">
        <div class="space-y-3 text-sm">
          <div
            v-for="item in items"
            :key="item.product.id"
            class="flex justify-between"
          >
            <span class="text-text-secondary"
              >{{ item.product.name }} × {{ item.quantity }}</span
            >
            <span class="font-medium tabular-nums">{{
              formatCurrency(item.product.price * item.quantity, currency)
            }}</span>
          </div>
          <div class="border-t border-border pt-3 space-y-2">
            <div class="flex justify-between">
              <span class="text-text-muted">Subtotal</span>
              <span class="tabular-nums">{{
                formatCurrency(subtotal, currency)
              }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-border">
              <span class="font-semibold text-text">Total</span>
              <span class="font-bold text-text text-base tabular-nums">{{
                formatCurrency(subtotal, currency)
              }}</span>
            </div>
          </div>
        </div>
      </UiAppCard>
    </div>

    <!-- Pay button -->
    <UiAppButton type="submit" size="lg" :loading="processing" class="w-full">
      Pay {{ formatCurrency(subtotal, currency) }}
    </UiAppButton>
  </form>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    processing?: boolean;
    errors?: Record<string, string>;
  }>(),
  {
    processing: false,
    errors: () => ({}),
  },
);

defineEmits<{
  submit: [];
}>();

const { items, subtotal } = useCart();
const { formatCurrency } = useCurrency();
const { currency } = useStoreSettings();

const customerInfo = reactive({
  name: "",
  email: "",
});
</script>
