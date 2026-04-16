<template>
  <NuxtLayout name="store">
    <div class="max-w-2xl mx-auto">
      <h1 class="text-2xl font-bold text-text mb-8">Checkout</h1>

      <div v-if="!items.length" class="text-center py-16">
        <p class="text-text-muted mb-4">Your cart is empty.</p>
        <UiAppButton to="/store">← Back to Store</UiAppButton>
      </div>

      <!-- Guest checkout disabled -->
      <div
        v-else-if="!guestCheckoutEnabled && !isAuthenticated"
        class="text-center py-16 space-y-4"
      >
        <div
          class="w-14 h-14 mx-auto rounded-2xl bg-warning/10 flex items-center justify-center text-2xl"
        >
          🔒
        </div>
        <h2 class="text-lg font-semibold text-text">Account Required</h2>
        <p class="text-sm text-text-muted max-w-sm mx-auto">
          This store requires you to log in before checking out. Please sign in
          or create an account to continue.
        </p>
        <div class="flex items-center justify-center gap-3 pt-2">
          <UiAppButton variant="outline" to="/store"
            >← Back to Store</UiAppButton
          >
          <UiAppButton to="/auth/login">Sign In</UiAppButton>
        </div>
      </div>

      <StoreCheckoutForm
        v-else
        :processing="processing"
        :errors="errors"
        @submit="handleCheckout"
      />
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ["module-guard"],
  requiredModule: "storefront",
});

const api = useApi();
const { items, subtotal, clearCart } = useCart();
const { guestCheckoutEnabled, loadSettings } = useStoreSettings();
const { isAuthenticated } = useAuth();

const processing = ref(false);
const errors = ref<Record<string, string>>({});

onMounted(() => loadSettings());

async function handleCheckout() {
  processing.value = true;
  errors.value = {};
  try {
    const res = await api<{
      order_number: string;
      stripe_checkout_url?: string;
    }>("/api/store/checkout", {
      method: "POST",
      body: {
        items: items.value.map((i) => ({
          product_id: i.product.id,
          quantity: i.quantity,
        })),
      },
    });

    // If Stripe checkout URL is returned, redirect to Stripe
    if (res.stripe_checkout_url) {
      window.location.href = res.stripe_checkout_url;
    } else {
      clearCart();
      navigateTo(`/store/order-confirmation?order=${res.order_number}`);
    }
  } catch (e: any) {
    if (e?.data?.errors) errors.value = e.data.errors;
    useToast().apiError(e, "Checkout failed. Please try again.");
  } finally {
    processing.value = false;
  }
}
</script>
