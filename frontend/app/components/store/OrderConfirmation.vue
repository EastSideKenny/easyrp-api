<template>
  <div class="max-w-lg mx-auto text-center space-y-6 py-12">
    <div
      class="w-20 h-20 mx-auto rounded-full bg-success/10 flex items-center justify-center"
    >
      <svg
        class="w-10 h-10 text-success"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M5 13l4 4L19 7"
        />
      </svg>
    </div>

    <div class="space-y-2">
      <h1 class="text-2xl font-bold text-text">Order Confirmed!</h1>
      <p class="text-text-secondary">
        Thank you for your purchase. Your order
        <span v-if="orderNumber" class="font-semibold text-primary">{{
          orderNumber
        }}</span>
        has been placed.
      </p>
    </div>

    <UiAppCard :no-padding="false">
      <div class="text-sm space-y-3">
        <div class="flex justify-between">
          <span class="text-text-muted">Order Number</span>
          <span class="font-semibold text-text">{{ orderNumber }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-text-muted">Total</span>
          <span class="font-semibold text-text tabular-nums">{{
            formatCurrency(total)
          }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-text-muted">Email</span>
          <span class="text-text">{{ email }}</span>
        </div>
      </div>
    </UiAppCard>

    <p class="text-sm text-text-muted">
      A confirmation email has been sent. You can track your order status using
      the order number above.
    </p>

    <NuxtLink
      to="/store"
      class="inline-flex items-center gap-2 bg-primary text-white text-sm font-semibold px-6 py-2.5 rounded-xl hover:bg-primary-dark transition-all duration-200"
    >
      ← Continue Shopping
    </NuxtLink>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  orderNumber: string;
  total: number;
  email: string;
}>();

const { formatCurrency } = useCurrency();
</script>
