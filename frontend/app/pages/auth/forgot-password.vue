<template>
  <NuxtLayout name="auth">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-text">
          Reset your password
        </h1>
        <p class="mt-1.5 text-sm text-text-secondary">
          Enter your email address and we'll send you a link to reset your
          password.
        </p>
      </div>

      <!-- Success message -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
      >
        <div
          v-if="sent"
          class="flex items-start gap-3 bg-success/5 border border-success/10 rounded-xl px-4 py-3"
        >
          <svg
            class="w-5 h-5 text-success shrink-0 mt-0.5"
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
          <div>
            <p class="text-sm font-medium text-success">Check your email</p>
            <p class="text-sm text-text-secondary mt-0.5">
              We've sent a password reset link to
              <strong class="text-text">{{ form.email }}</strong
              >. Check your spam folder if you don't see it.
            </p>
          </div>
        </div>
      </Transition>

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
      <form v-if="!sent" class="space-y-4" @submit.prevent="handleResetRequest">
        <UiAppFormField label="Email address" id="email">
          <input
            id="email"
            v-model="form.email"
            type="email"
            autocomplete="email"
            required
            placeholder="you@company.com"
            class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
          />
        </UiAppFormField>

        <UiAppButton
          type="submit"
          :loading="loading"
          :disabled="loading"
          class="w-full"
        >
          {{ loading ? "Sending…" : "Send reset link" }}
        </UiAppButton>
      </form>

      <!-- Resend option (shown after success) -->
      <div v-if="sent" class="space-y-4">
        <UiAppButton
          variant="outline"
          :disabled="resendCooldown > 0"
          class="w-full"
          @click="handleResetRequest"
        >
          {{
            resendCooldown > 0
              ? `Resend in ${resendCooldown}s`
              : "Resend reset link"
          }}
        </UiAppButton>
      </div>

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

// If already authenticated, redirect away from forgot-password
const { isAuthenticated, user } = useAuth();
const { hasTenant } = useTenant();
const { redirectAfterLogin } = useWorkspaces();

if (isAuthenticated.value && user.value) {
  if (hasTenant.value) {
    navigateTo("/dashboard", { replace: true });
  } else {
    redirectAfterLogin(user.value);
  }
}

const form = reactive({
  email: "",
});

const loading = ref(false);
const error = ref("");
const sent = ref(false);
const resendCooldown = ref(0);

let cooldownTimer: ReturnType<typeof setInterval> | null = null;

function startCooldown() {
  resendCooldown.value = 60;
  cooldownTimer = setInterval(() => {
    resendCooldown.value--;
    if (resendCooldown.value <= 0 && cooldownTimer) {
      clearInterval(cooldownTimer);
      cooldownTimer = null;
    }
  }, 1000);
}

onUnmounted(() => {
  if (cooldownTimer) clearInterval(cooldownTimer);
});

const { authFetch } = useAuth();

async function handleResetRequest() {
  loading.value = true;
  error.value = "";

  try {
    await authFetch("/api/forgot-password", {
      method: "POST",
      body: { email: form.email },
    });

    sent.value = true;
    startCooldown();
  } catch (err: any) {
    if (err?.response?.status === 422) {
      const data = err.response._data;
      error.value =
        data?.errors?.email?.[0] || "Please enter a valid email address.";
    } else if (err?.response?.status === 429) {
      error.value =
        "Too many requests. Please wait a moment before trying again.";
    } else {
      error.value = "Something went wrong. Please try again later.";
    }
  } finally {
    loading.value = false;
  }
}
</script>
