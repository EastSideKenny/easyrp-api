<template>
  <NuxtLayout name="default" page-title="New Customer">
    <UiAppCard title="Create Customer">
      <CustomersCustomerForm
        :saving="saving"
        submit-label="Create Customer"
        :errors="errors"
        @submit="handleSubmit"
        @cancel="navigateTo('/customers')"
      />
    </UiAppCard>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { CustomerFormData } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth"],
});

const { createCustomer } = useCustomers();
const toast = useToast();
const saving = ref(false);
const errors = ref<Record<string, string>>({});

async function handleSubmit(data: CustomerFormData) {
  saving.value = true;
  errors.value = {};
  try {
    const customer = await createCustomer(data);
    toast.success("Customer created successfully.");
    navigateTo(`/customers/${customer.id}`);
  } catch (e: any) {
    if (e?.data?.errors) errors.value = e.data.errors;
    toast.apiError(e, "Failed to create customer.");
  } finally {
    saving.value = false;
  }
}
</script>
