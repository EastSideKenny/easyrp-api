<template>
  <NuxtLayout name="default" page-title="Create Offer">
    <UiAppCard title="Create Offer">
      <OffersOfferForm
        :customers="customers"
        :products="products"
        :saving="saving"
        submit-label="Create Offer"
        :errors="errors"
        @submit="handleSubmit"
        @cancel="navigateTo('/offers')"
      />
    </UiAppCard>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { OfferFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "invoices",
});

const { createOffer } = useOffers();
const { customers, fetchCustomers } = useCustomers();
const { products, fetchProducts } = useProducts();
const toast = useToast();
const saving = ref(false);
const errors = ref<Record<string, string>>({});

await Promise.all([
  fetchCustomers({ per_page: 100 }),
  fetchProducts({ per_page: 100 }),
]);

async function handleSubmit(data: OfferFormData) {
  saving.value = true;
  errors.value = {};
  try {
    const offer = await createOffer(data);
    toast.success("Offer created successfully.");
    navigateTo(`/offers/${offer.id}`);
  } catch (e: any) {
    if (e?.data?.errors) errors.value = e.data.errors;
    toast.apiError(e, "Failed to create offer.");
  } finally {
    saving.value = false;
  }
}
</script>
