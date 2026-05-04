<template>
  <NuxtLayout name="auth">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-text">
          Create your account
        </h1>
        <p class="mt-1.5 text-sm text-text-secondary">
          Start your 14-day free trial. No credit card required.
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
      <form class="space-y-4" @submit.prevent="handleRegister">
        <UiAppFormField label="Full name" id="name">
          <input
            id="name"
            v-model="form.name"
            type="text"
            autocomplete="name"
            required
            placeholder="Jamie Mitchell"
            class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
          />
        </UiAppFormField>

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
          <label for="password" class="block text-sm font-medium text-text"
            >Password</label
          >
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="new-password"
              required
              minlength="8"
              placeholder="Min. 8 characters"
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

          <!-- Password strength indicator -->
          <div v-if="form.password" class="flex gap-1 mt-2">
            <div
              v-for="i in 4"
              :key="i"
              class="flex-1 h-1 rounded-full transition-colors duration-300"
              :class="i <= passwordStrength ? strengthColor : 'bg-border'"
            />
          </div>
          <p
            v-if="form.password"
            class="text-xs"
            :class="passwordStrength >= 3 ? 'text-success' : 'text-text-muted'"
          >
            {{ strengthLabel }}
          </p>
        </div>

        <div class="flex items-start gap-2 pt-1">
          <input
            id="terms"
            v-model="form.terms"
            type="checkbox"
            required
            class="w-4 h-4 mt-0.5 rounded border-border text-primary focus:ring-primary/20 cursor-pointer"
          />
          <label
            for="terms"
            class="text-sm text-text-secondary cursor-pointer select-none leading-snug"
          >
            I agree to the
            <button
              type="button"
              class="text-primary hover:underline"
              @click="showTermsModal = true"
            >
              Terms of Service
            </button>
            and
            <button
              type="button"
              class="text-primary hover:underline"
              @click="showPrivacyModal = true"
            >
              Privacy Policy
            </button>
          </label>
        </div>

        <UiAppButton
          type="submit"
          :loading="loading"
          :disabled="loading || !form.terms"
          class="w-full"
        >
          {{ loading ? "Creating account…" : "Create account" }}
        </UiAppButton>
      </form>

      <!-- Sign in link -->
      <p class="text-center text-sm text-text-secondary">
        Already have an account?
        <NuxtLink
          to="/auth/login"
          class="text-primary font-medium hover:text-primary-dark transition-colors"
        >
          Sign in
        </NuxtLink>
      </p>
    </div>

    <!-- Terms modal -->
    <div
      v-if="showTermsModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
      @click.self="showTermsModal = false"
    >
      <div
        class="w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl bg-surface border border-border shadow-elevated p-6"
      >
        <div class="flex items-start justify-between gap-4 mb-4">
          <h2 class="text-xl font-bold text-text">Terms of Service</h2>
          <button
            type="button"
            class="text-text-muted hover:text-text"
            @click="showTermsModal = false"
          >
            Close
          </button>
        </div>

        <div class="space-y-4 text-sm text-text-secondary leading-relaxed">
          <p>
            EasyRP provides business software for invoicing, orders, inventory,
            and storefront management. By creating an account, you agree to use
            the service lawfully and protect your login credentials.
          </p>
          <p>
            You are responsible for the data you enter, including customer and
            financial records. We process your data to provide the service,
            secure accounts, and support billing.
          </p>
          <p>
            Paid plans renew automatically unless canceled before the next
            billing date. You can manage your subscription from your account
            settings.
          </p>
          <p>
            We may suspend accounts for abuse, fraud, or serious violations.
            You can request account closure and data export through support.
          </p>
          <p class="text-text">
            By continuing, you confirm that you have the authority to use this
            service on behalf of your business.
          </p>
        </div>
      </div>
    </div>

    <!-- Privacy modal -->
    <div
      v-if="showPrivacyModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
      @click.self="showPrivacyModal = false"
    >
      <div
        class="w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl bg-surface border border-border shadow-elevated p-6"
      >
        <div class="flex items-start justify-between gap-4 mb-4">
          <h2 class="text-xl font-bold text-text">Privacy Policy</h2>
          <button
            type="button"
            class="text-text-muted hover:text-text"
            @click="showPrivacyModal = false"
          >
            Close
          </button>
        </div>

        <div class="space-y-4 text-sm text-text-secondary leading-relaxed">
          <p>
            We collect account details (such as name and email), workspace
            settings, and data you store in EasyRP to deliver the platform.
          </p>
          <p>
            We process personal data under GDPR legal bases including contract
            performance, legitimate interests (security and fraud prevention),
            and legal obligations.
          </p>
          <p>
            We use trusted subprocessors (for example hosting, email, and
            payment providers) only as needed to run the service. Data is
            protected with access controls and security monitoring.
          </p>
          <p>
            You can request access, correction, export, restriction, or
            deletion of your personal data. You can also object to processing
            where applicable.
          </p>
          <p>
            For privacy requests, contact support and include your account email
            so we can verify ownership and respond within GDPR timelines.
          </p>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
});

// If already authenticated, redirect away from register
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
  name: "",
  email: "",
  password: "",
  terms: false,
});

const loading = ref(false);
const error = ref("");
const showPassword = ref(false);
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);

const passwordStrength = computed(() => {
  const p = form.password;
  if (!p) return 0;
  let score = 0;
  if (p.length >= 8) score++;
  if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++;
  if (/\d/.test(p)) score++;
  if (/[^A-Za-z0-9]/.test(p)) score++;
  return score;
});

const strengthColor = computed(() => {
  const colors: Record<number, string> = {
    1: "bg-danger",
    2: "bg-warning",
    3: "bg-info",
    4: "bg-success",
  };
  return colors[passwordStrength.value] || "bg-border";
});

const strengthLabel = computed(() => {
  const labels: Record<number, string> = {
    0: "",
    1: "Weak — add uppercase, numbers, or symbols",
    2: "Fair — getting there",
    3: "Good — looking solid",
    4: "Strong — excellent password",
  };
  return labels[passwordStrength.value] || "";
});

const { login, authFetch } = useAuth();

async function handleRegister() {
  loading.value = true;
  error.value = "";

  try {
    // Step 1: Register the user (creates account only, no tenant yet)
    await authFetch("/api/register", {
      method: "POST",
      body: {
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password,
      },
    });

    // Step 2: Log in — stores the API token and fetches user identity
    await login({ email: form.email, password: form.password });

    // Step 3: Send to onboarding wizard to create their first workspace
    await navigateTo("/onboarding");
  } catch (err: any) {
    if (err?.response?.status === 422) {
      const data = err.response._data;
      if (data?.errors) {
        // Show first validation error
        const fields = Object.keys(data.errors);
        const firstField = fields[0];
        error.value = firstField
          ? data.errors[firstField]?.[0] || "Validation error."
          : "Validation error.";
      } else {
        error.value =
          data?.message || "Please check your information and try again.";
      }
    } else {
      error.value = "Something went wrong. Please try again later.";
    }
  } finally {
    loading.value = false;
  }
}
</script>
