<template>
  <NuxtLayout name="default" page-title="Payment Details">
    <div
      v-if="loading"
      class="flex items-center justify-center py-20 text-text-muted"
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
    <PaymentsPaymentDetail v-else :payment="payment" @delete="handleDelete" />
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "invoices",
});

const route = useRoute();
const { payment, loading, fetchPayment, deletePayment } = usePayments();

const toast = useToast();
const paymentId = computed(() => Number(route.params.id));

onMounted(() => fetchPayment(paymentId.value));

async function handleDelete() {
  toast.error("Recorded payments cannot be deleted for audit purposes.");
}
</script>
