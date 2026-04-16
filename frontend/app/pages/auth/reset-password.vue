<template>
  <NuxtLayout name="auth">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-text">
          Set a new password
        </h1>
        <p class="mt-1.5 text-sm text-text-secondary">
          Enter your new password below.
        </p>
      </div>

      <!-- Error alert -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
      >
        <div
          v-if="error"
          class="flex items-start gap-3 bg-danger/5 border border-danger/10 rounded-xl px-4 py-3"
        >
          <svg
            class="w-5 h-5 text-danger shrink-0 mt-0.5"
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
          <p class="text-sm text-danger">{{ error }}</p>
        </div>
      </Transition>

      <!-- Form -->
      <form class="space-y-4" @submit.prevent="handleReset">
        <UiAppFormField
          label="New password"
          id="password"
          :error="fieldErrors.password"
        >
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="new-password"
              required
              minlength="8"
              placeholder="••••••••"
              class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 pr-10 text-sm text-text placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text transition-colors"
              @click="showPassword = !showPassword"
            >
              <svg
                v-if="!showPassword"
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              <svg
                v-else
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
                />
              </svg>
            </button>
          </div>
        </UiAppFormField>

        <UiAppFormField
          label="Confirm password"
          id="password_confirmation"
          :error="fieldErrors.password_confirmation"
        >
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            autocomplete="new-password"
            required
            minlength="8"
            placeholder="••••••••"
            class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
          />
        </UiAppFormField>

        <UiAppButton
          type="submit"
          :loading="loading"
          :disabled="loading"
          class="w-full"
        >
          {{ loading ? "Resetting…" : "Reset password" }}
        </UiAppButton>
      </form>

      <!-- Back to login -->
      <div class="text-center">
        <NuxtLink
          to="/auth/login"
          class="inline-flex items-center gap-1.5 text-sm text-text-secondary hover:text-text transition-colors"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
            />
          </svg>
          Back to sign in
        </NuxtLink>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
});

const route = useRoute();
const { authFetch } = useAuth();

const token = computed(() => (route.query.token as string) ?? "");
const email = computed(() => (route.query.email as string) ?? "");

const form = reactive({
  password: "",
  password_confirmation: "",
});

const loading = ref(false);
const error = ref("");
const fieldErrors = ref<Record<string, string>>({});
const showPassword = ref(false);

async function handleReset() {
  loading.value = true;
  error.value = "";
  fieldErrors.value = {};

  try {
    await authFetch("/api/reset-password", {
      method: "POST",
      body: {
        token: token.value,
        email: email.value,
        password: form.password,
        password_confirmation: form.password_confirmation,
      },
    });

    await navigateTo("/auth/login?reset=success");
  } catch (err: any) {
    if (err?.response?.status === 422) {
      const data = err.response._data;
      if (data?.errors) {
        // Map first error per field
        for (const [key, messages] of Object.entries(data.errors)) {
          fieldErrors.value[key] = (messages as string[])[0] ?? "";
        }
      }
      error.value = data?.message || "Please check the form and try again.";
    } else if (err?.response?.status === 429) {
      error.value =
        "Too many requests. Please wait a moment before trying again.";
    } else {
      error.value =
        "This password reset link is invalid or has expired. Please request a new one.";
    }
  } finally {
    loading.value = false;
  }
}
</script>
