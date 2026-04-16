<template>
  <NuxtLayout name="auth">
    <div class="space-y-6">
      <div
        v-if="loading"
        class="flex flex-col items-center justify-center py-12 text-text-muted"
      >
        <svg
          class="w-8 h-8 animate-spin text-primary mb-3"
          fill="none"
          viewBox="0 0 24 24"
        >
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
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
        <p class="text-sm">Processing your response…</p>
      </div>
      <div v-else class="space-y-6">
        <div
          v-if="result === 'success'"
          class="flex flex-col items-center text-center py-8"
        >
          <div
            class="w-14 h-14 rounded-full bg-success/10 flex items-center justify-center mb-4"
          >
            <svg
              class="w-7 h-7 text-success"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <h1 class="text-2xl font-extrabold tracking-tight text-text">
            Offer {{ actionLabel }}
          </h1>
          <p class="mt-1.5 text-sm text-text-secondary">
            The offer has been {{ actionLabel }} successfully. The sender has
            been notified.
          </p>
        </div>
        <div
          v-else-if="result === 'already_responded'"
          class="flex flex-col items-center text-center py-8"
        >
          <div
            class="w-14 h-14 rounded-full bg-warning/10 flex items-center justify-center mb-4"
          >
            <svg
              class="w-7 h-7 text-warning"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
              />
            </svg>
          </div>
          <h1 class="text-2xl font-extrabold tracking-tight text-text">
            Already responded
          </h1>
          <p class="mt-1.5 text-sm text-text-secondary">{{ errorMessage }}</p>
        </div>
        <div
          v-else-if="result === 'invalid_token'"
          class="flex flex-col items-center text-center py-8"
        >
          <div
            class="w-14 h-14 rounded-full bg-danger/10 flex items-center justify-center mb-4"
          >
            <svg
              class="w-7 h-7 text-danger"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
              />
            </svg>
          </div>
          <h1 class="text-2xl font-extrabold tracking-tight text-text">
            Invalid or expired link
          </h1>
          <p class="mt-1.5 text-sm text-text-secondary">
            This link is invalid or has expired. Please contact the sender for a
            new offer.
          </p>
        </div>
        <div
          v-else-if="result === 'error'"
          class="flex flex-col items-center text-center py-8"
        >
          <div
            class="w-14 h-14 rounded-full bg-danger/10 flex items-center justify-center mb-4"
          >
            <svg
              class="w-7 h-7 text-danger"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
              />
            </svg>
          </div>
          <h1 class="text-2xl font-extrabold tracking-tight text-text">
            Something went wrong
          </h1>
          <p class="mt-1.5 text-sm text-text-secondary">{{ errorMessage }}</p>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({ layout: false });
const route = useRoute();
const config = useRuntimeConfig();
const baseUrl = config.public.apiBaseUrl as string;
const offerId = route.params.id as string;
const action = (route.query.action as string) ?? "";
const token = (route.query.token as string) ?? "";
const loading = ref(true);
const result = ref<"success" | "already_responded" | "invalid_token" | "error">(
  "error",
);
const errorMessage = ref("Something went wrong. Please try again later.");
const actionLabel = computed(() =>
  action === "accept" ? "accepted" : "declined",
);

onMounted(async () => {
  if (!action || !token || !["accept", "decline"].includes(action)) {
    loading.value = false;
    result.value = "error";
    errorMessage.value = "Invalid link. Missing action or token.";
    return;
  }
  try {
    await $fetch(`/api/offers/${offerId}/respond`, {
      baseURL: baseUrl,
      method: "POST",
      headers: { Accept: "application/json" },
      body: { action, token },
    });
    result.value = "success";
  } catch (err: any) {
    const status = err?.response?.status;
    const data = err?.response?._data;
    if (status === 422) {
      result.value = "already_responded";
      errorMessage.value =
        data?.message ||
        "This offer has already been responded to or has expired.";
    } else if (status === 403) {
      result.value = "invalid_token";
    } else {
      result.value = "error";
      errorMessage.value =
        data?.message || "Something went wrong. Please try again later.";
    }
  } finally {
    loading.value = false;
  }
});
</script>
