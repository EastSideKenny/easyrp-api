<template>
  <NuxtLayout name="default" page-title="Stock Movement History">
    <InventoryStockHistory :movements="movements" :loading="loading" />
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ["auth", "module-guard"],
  requiredModule: "products",
});

const { movements, loading, fetchMovements } = useInventory();

await fetchMovements();
</script>
