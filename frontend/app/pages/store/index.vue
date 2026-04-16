<template>
  <NuxtLayout name="store">
    <template #header-actions>
      <button
        class="relative text-text-secondary hover:text-text p-2 rounded-lg hover:bg-surface-alt transition-colors"
        @click="cartOpen = true"
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
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"
          />
        </svg>
        <span
          v-if="itemCount > 0"
          class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-white text-[10px] font-bold rounded-full flex items-center justify-center"
        >
          {{ itemCount }}
        </span>
      </button>
    </template>

    <StoreProductGrid
      :products="products"
      :loading="loading"
      v-model:search="search"
      v-model:product-type="productType"
      @add-to-cart="addItem($event)"
    />

    <StoreCartDrawer :open="cartOpen" @close="cartOpen = false" />
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { Product } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["module-guard"],
  requiredModule: "storefront",
});

const config = useRuntimeConfig();
const baseUrl = config.public.apiBaseUrl as string;
const { tenantSlug } = useTenant();
const { items, itemCount, addItem } = useCart();

const products = ref<Product[]>([]);
const loading = ref(true);
const search = ref("");
const productType = ref("");
const cartOpen = ref(false);

const debouncedSearch = useDebounce(search);

async function loadProducts() {
  const slug = tenantSlug.value;
  if (!slug) return;

  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (debouncedSearch.value) params.append("search", debouncedSearch.value);
    if (productType.value) params.append("type", productType.value);
    const query = params.toString() ? `?${params}` : "";
    const res = await $fetch<any>(`/api/storefront/${slug}/products${query}`, {
      baseURL: baseUrl,
      headers: { Accept: "application/json" },
    });
    if (Array.isArray(res)) {
      products.value = res;
    } else if (res?.data && Array.isArray(res.data)) {
      products.value = res.data;
    } else {
      console.warn("[Store] Unexpected products response:", res);
      products.value = [];
    }
  } catch (e) {
    console.error("[Store] Failed to fetch products:", e);
    products.value = [];
  } finally {
    loading.value = false;
  }
}

// Fetch on client mount and when filters change
onMounted(() => loadProducts());

watch([debouncedSearch, productType], () => loadProducts());
</script>
