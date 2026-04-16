<template>
  <!-- Overlay -->
  <Transition
    enter-active-class="transition duration-300"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open"
      class="fixed inset-0 z-40 bg-text/20 backdrop-blur-sm"
      @click="$emit('close')"
    />
  </Transition>

  <!-- Drawer -->
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="translate-x-full"
    enter-to-class="translate-x-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="translate-x-0"
    leave-to-class="translate-x-full"
  >
    <aside
      v-if="open"
      class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-surface border-l border-border shadow-elevated flex flex-col"
    >
      <!-- Header -->
      <div
        class="flex items-center justify-between px-6 py-4 border-b border-border"
      >
        <h3 class="text-lg font-semibold text-text">Cart ({{ itemCount }})</h3>
        <button
          class="text-text-muted hover:text-text transition-colors"
          @click="$emit('close')"
        >
          <svg
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Items -->
      <div class="flex-1 overflow-y-auto px-6">
        <div v-if="items.length">
          <StoreCartItem
            v-for="item in items"
            :key="item.product.id"
            :item="item"
            @update-qty="updateQuantity"
            @remove="removeItem"
          />
        </div>
        <div
          v-else
          class="flex flex-col items-center justify-center h-full text-center"
        >
          <div class="w-16 h-16 rounded-2xl bg-surface-alt border border-border flex items-center justify-center mb-4">
            <ShoppingCart class="w-7 h-7 text-text-muted" />
          </div>
          <p class="text-sm font-medium text-text">Your cart is empty</p>
          <p class="text-xs text-text-muted mt-1">
            Browse our products and add items to your cart.
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div
        v-if="items.length"
        class="border-t border-border px-6 py-5 space-y-4"
      >
        <div class="flex justify-between text-sm">
          <span class="text-text-muted">Subtotal</span>
          <span class="font-semibold text-text tabular-nums">{{
            formatCurrency(subtotal, currency)
          }}</span>
        </div>
        <p class="text-xs text-text-muted">
          Shipping and taxes calculated at checkout.
        </p>
        <NuxtLink
          to="/store/checkout"
          class="block w-full bg-primary text-white text-center text-sm font-semibold px-4 py-3 rounded-xl hover:bg-primary-dark hover:shadow-elevated transition-all duration-200"
          @click="$emit('close')"
        >
          Proceed to Checkout
        </NuxtLink>
      </div>
    </aside>
  </Transition>
</template>

<script setup lang="ts">
defineProps<{
  open: boolean;
}>();

defineEmits<{
  close: [];
}>();

import { ShoppingCart } from "lucide-vue-next";

const { items, itemCount, subtotal, updateQuantity, removeItem } = useCart();
const { formatCurrency } = useCurrency();
const { currency } = useStoreSettings();
</script>
