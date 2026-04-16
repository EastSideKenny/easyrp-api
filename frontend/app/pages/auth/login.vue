<template>
  <NuxtLayout name="auth">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-text">
          Welcome back
        </h1>
        <p class="mt-1.5 text-sm text-text-secondary">
          Sign in to your EasyRP account to continue.
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
      <form class="space-y-4" @submit.prevent="handleLogin">
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

        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-text"
              >Password</label
            >
            <NuxtLink
              to="/auth/forgot-password"
              class="text-xs text-primary hover:text-primary-dark transition-colors"
            >
              Forgot password?
            </NuxtLink>
          </div>
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              required
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
        </div>

        <div class="flex items-center gap-2">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="w-4 h-4 rounded border-border text-primary focus:ring-primary/20 cursor-pointer"
          />
          <label
            for="remember"
            class="text-sm text-text-secondary cursor-pointer select-none"
          >
            Remember me for 30 days
          </label>
        </div>

        <UiAppButton
          type="submit"
          :loading="loading"
          :disabled="loading"
          class="w-full"
        >
          {{ loading ? "Signing in…" : "Sign in" }}
        </UiAppButton>
      </form>

      <!-- Divider -->
      <div class="flex items-center gap-3">
        <div class="flex-1 h-px bg-border" />
        <span class="text-xs text-text-muted">or</span>
        <div class="flex-1 h-px bg-border" />
      </div>

      <!-- Social login buttons -->
      <div class="space-y-2.5">
        <UiAppButton variant="outline" class="w-full">
          <svg class="w-4 h-4" viewBox="0 0 24 24">
            <path
              d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"
              fill="#4285F4"
            />
            <path
              d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
              fill="#34A853"
            />
            <path
              d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
              fill="#FBBC05"
            />
            <path
              d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
              fill="#EA4335"
            />
          </svg>
          Continue with Google
        </UiAppButton>
      </div>

      <!-- Sign up link -->
      <p class="text-center text-sm text-text-secondary">
        Don't have an account?
        <NuxtLink
          to="/auth/register"
          class="text-primary font-medium hover:text-primary-dark transition-colors"
        >
          Sign up for free
        </NuxtLink>
      </p>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
});

// If already authenticated, redirect away from login
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
  password: "",
  remember: false,
});

const loading = ref(false);
const error = ref("");
const showPassword = ref(false);

const { login } = useAuth();

async function handleLogin() {
  loading.value = true;
  error.value = "";

  try {
    const loggedInUser = await login({
      email: form.email,
      password: form.password,
    });

    // If we're on a tenant subdomain, go straight to the dashboard.
    // If we're on the root domain, check if the user already has a tenant.
    if (hasTenant.value) {
      await navigateTo("/dashboard");
    } else {
      redirectAfterLogin(loggedInUser);
    }
  } catch (err: any) {
    if (err?.response?.status === 422) {
      error.value = "Invalid email or password. Please try again.";
    } else if (err?.response?.status === 429) {
      error.value =
        "Too many login attempts. Please wait a moment and try again.";
    } else {
      error.value =
        "Something went wrong. Please check your connection and try again.";
    }
  } finally {
    loading.value = false;
  }
}
</script>
