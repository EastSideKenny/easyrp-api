<template>
  <NuxtLayout name="store">
    <StoreOrderConfirmation
      :order-number="orderNumber"
      :total="total"
      :email="email"
    />
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ["module-guard"],
  requiredModule: "storefront",
});

const route = useRoute();
const orderNumber = String(route.query.order ?? "");
const total = Number(route.query.total ?? 0);
const email = String(route.query.email ?? "");
</script>
