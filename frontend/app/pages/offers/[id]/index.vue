<template>
  <NuxtLayout name="default" page-title="Offer Details">
    <div
      v-if="loading"
      class="flex items-center justify-center py-20 text-text-muted"
    >
      Loading…
    </div>
    <OffersOfferDetail
      v-else
      :offer="offer"
      @send="handleSend"
      @accept="handleAccept"
      @decline="handleDecline"
      @download-pdf="handleDownloadPdf"
      @edit="showEditModal = true"
      @delete="handleDelete"
    />

    <!-- Edit Modal -->
    <UiAppModal v-model="showEditModal" title="Edit Offer" size="xl">
      <OffersOfferForm
        v-if="offer"
        :customers="customers"
        :products="products"
        :initial-data="offerFormData"
        :saving="editSaving"
        submit-label="Update Offer"
        :errors="editErrors"
        @submit="handleUpdate"
        @cancel="showEditModal = false"
      />
    </UiAppModal>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { OfferFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "invoices",
});

const route = useRoute();
const {
  offer,
  loading,
  fetchOffer,
  updateOffer,
  sendOffer,
  acceptOffer,
  declineOffer,
  deleteOffer,
  downloadOfferPdf,
} = useOffers();
const { customers, fetchCustomers } = useCustomers();
const { products, fetchProducts } = useProducts();

const showEditModal = ref(false);
const editSaving = ref(false);
const editErrors = ref<Record<string, string>>({});
const toast = useToast();

await fetchOffer(Number(route.params.id));

// Lazy-load customers & products when edit modal opens
watch(showEditModal, async (open) => {
  if (open && customers.value.length === 0) {
    await Promise.all([
      fetchCustomers({ per_page: 100 }),
      fetchProducts({ per_page: 100 }),
    ]);
  }
});

// Map the full Offer to OfferFormData shape for the form
const offerFormData = computed<Partial<OfferFormData>>(() => {
  if (!offer.value) return {};
  return {
    customer_id: offer.value.customer_id,
    issue_date: offer.value.issue_date,
    valid_until: offer.value.valid_until,
    notes: offer.value.notes ?? "",
    items: offer.value.items.map((item) => ({
      product_id: item.product_id,
      description: item.description,
      quantity: item.quantity,
      unit_price: item.unit_price,
      tax_rate: item.tax_rate,
      line_total: item.line_total,
    })),
  };
});

async function handleUpdate(data: OfferFormData) {
  editSaving.value = true;
  editErrors.value = {};
  try {
    await updateOffer(Number(route.params.id), data);
    toast.success("Offer updated.");
    showEditModal.value = false;
    await fetchOffer(Number(route.params.id));
  } catch (e: any) {
    if (e?.data?.errors) editErrors.value = e.data.errors;
    toast.apiError(e, "Failed to update offer.");
  } finally {
    editSaving.value = false;
  }
}

async function handleSend() {
  try {
    await sendOffer(Number(route.params.id));
    toast.success("Offer sent.");
    await fetchOffer(Number(route.params.id));
  } catch (e: any) {
    toast.apiError(e, "Failed to send offer.");
  }
}

async function handleAccept() {
  if (!confirm("Accept this offer? This will create a draft invoice from it."))
    return;
  try {
    await acceptOffer(Number(route.params.id));
    toast.success("Offer accepted — draft invoice created.");
    await fetchOffer(Number(route.params.id));
  } catch (e: any) {
    toast.apiError(e, "Failed to accept offer.");
  }
}

async function handleDecline() {
  if (!confirm("Decline this offer?")) return;
  try {
    await declineOffer(Number(route.params.id));
    toast.success("Offer declined.");
    await fetchOffer(Number(route.params.id));
  } catch (e: any) {
    toast.apiError(e, "Failed to decline offer.");
  }
}

async function handleDownloadPdf() {
  try {
    await downloadOfferPdf(Number(route.params.id));
  } catch (e: any) {
    toast.apiError(e, "Failed to download PDF.");
  }
}

async function handleDelete() {
  if (offer.value?.status === "accepted") {
    toast.error("Accepted offers cannot be deleted.");
    return;
  }
  if (!confirm("Delete this offer?")) return;
  try {
    await deleteOffer(Number(route.params.id));
    toast.success("Offer deleted.");
    navigateTo("/offers");
  } catch (e: any) {
    toast.apiError(e, "Failed to delete offer.");
  }
}
</script>
