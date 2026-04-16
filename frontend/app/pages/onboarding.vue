<template>
  <NuxtLayout name="auth">
    <div class="space-y-6">
      <!-- Progress indicator -->
      <div class="flex items-center gap-2">
        <div
          v-for="s in totalSteps"
          :key="s"
          class="flex-1 h-1.5 rounded-full transition-colors duration-300"
          :class="s <= step ? 'bg-primary' : 'bg-border'"
        />
      </div>

      <!-- Step 1: Business info -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-x-4"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 -translate-x-4"
        mode="out-in"
      >
        <div v-if="step === 1" key="step-1" class="space-y-6">
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-text">
              Set up your workspace
            </h1>
            <p class="mt-1.5 text-sm text-text-secondary">
              Tell us about your business so we can tailor EasyRP for you.
            </p>
          </div>

          <form class="space-y-4" @submit.prevent="nextStep">
            <UiAppFormField
              label="Business name"
              id="business-name"
              hint="This will be your workspace name and URL."
            >
              <input
                id="business-name"
                v-model="form.businessName"
                type="text"
                required
                placeholder="Acme Inc."
                class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
              />
            </UiAppFormField>

            <!-- Subdomain preview -->
            <div
              v-if="form.businessName.trim()"
              class="flex items-center gap-1 text-xs text-text-muted"
            >
              <svg
                class="w-3.5 h-3.5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.07-9.07l-1.757 1.757a4.5 4.5 0 00-6.364 6.364l4.5 4.5a4.5 4.5 0 007.244 1.242"
                />
              </svg>
              <span>
                Your workspace URL:
                <span class="text-primary font-medium"
                  >{{ slugPreview }}.{{ appDomain }}</span
                >
              </span>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <UiAppFormField label="Industry" id="industry">
                <select
                  id="industry"
                  v-model="form.industry"
                  required
                  class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 appearance-none"
                >
                  <option value="" disabled>Select your industry</option>
                  <option value="retail">Retail & E-commerce</option>
                  <option value="manufacturing">Manufacturing</option>
                  <option value="wholesale">Wholesale & Distribution</option>
                  <option value="services">Professional Services</option>
                  <option value="food">Food & Beverage</option>
                  <option value="construction">Construction</option>
                  <option value="healthcare">Healthcare</option>
                  <option value="technology">Technology</option>
                  <option value="agriculture">Agriculture</option>
                  <option value="other">Other</option>
                </select>
              </UiAppFormField>

              <UiAppFormField
                label="Currency"
                id="currency"
                hint="Used on all invoices & orders."
              >
                <select
                  id="currency"
                  v-model="form.currency"
                  class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 appearance-none"
                >
                  <option value="USD">USD — US Dollar</option>
                  <option value="EUR">EUR — Euro</option>
                  <option value="GBP">GBP — British Pound</option>
                  <option value="AUD">AUD — Australian Dollar</option>
                  <option value="CAD">CAD — Canadian Dollar</option>
                  <option value="NZD">NZD — New Zealand Dollar</option>
                  <option value="CHF">CHF — Swiss Franc</option>
                  <option value="JPY">JPY — Japanese Yen</option>
                  <option value="SGD">SGD — Singapore Dollar</option>
                  <option value="HKD">HKD — Hong Kong Dollar</option>
                  <option value="SEK">SEK — Swedish Krona</option>
                  <option value="NOK">NOK — Norwegian Krone</option>
                  <option value="DKK">DKK — Danish Krone</option>
                  <option value="ZAR">ZAR — South African Rand</option>
                  <option value="MYR">MYR — Malaysian Ringgit</option>
                  <option value="AED">AED — UAE Dirham</option>
                </select>
              </UiAppFormField>
            </div>

            <UiAppButton
              type="submit"
              class="w-full"
              :disabled="!form.businessName.trim() || !form.industry"
            >
              Continue
            </UiAppButton>
          </form>
        </div>

        <!-- Step 2: Team size & role -->
        <div v-else-if="step === 2" key="step-2" class="space-y-6">
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-text">
              About your team
            </h1>
            <p class="mt-1.5 text-sm text-text-secondary">
              This helps us configure the right features for you.
            </p>
          </div>

          <form class="space-y-4" @submit.prevent="nextStep">
            <UiAppFormField label="Team size" id="team-size">
              <div class="grid grid-cols-2 gap-2">
                <button
                  v-for="option in teamSizeOptions"
                  :key="option.value"
                  type="button"
                  class="border rounded-xl px-4 py-3 text-sm font-medium transition-all duration-200 text-left"
                  :class="
                    form.teamSize === option.value
                      ? 'border-primary bg-primary/5 text-primary ring-2 ring-primary/20'
                      : 'border-border bg-surface text-text hover:border-primary/30 hover:bg-surface-alt'
                  "
                  @click="form.teamSize = option.value"
                >
                  <span class="block text-base mb-0.5">{{ option.icon }}</span>
                  {{ option.label }}
                </button>
              </div>
            </UiAppFormField>

            <UiAppFormField label="What's your primary role?" id="role">
              <select
                id="role"
                v-model="form.role"
                required
                class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 appearance-none"
              >
                <option value="" disabled>Select your role</option>
                <option value="owner">Business Owner / Founder</option>
                <option value="manager">Manager / Director</option>
                <option value="accountant">Accountant / Finance</option>
                <option value="operations">Operations</option>
                <option value="sales">Sales</option>
                <option value="other">Other</option>
              </select>
            </UiAppFormField>

            <div class="flex gap-2">
              <UiAppButton variant="outline" class="flex-1" @click="step = 1">
                Back
              </UiAppButton>
              <UiAppButton
                type="submit"
                class="flex-1"
                :disabled="!form.teamSize || !form.role"
              >
                Continue
              </UiAppButton>
            </div>
          </form>
        </div>

        <!-- Step 3: Plan selection -->
        <div v-else-if="step === 3" key="step-3" class="space-y-6">
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-text">
              Choose your plan
            </h1>
            <p class="mt-1.5 text-sm text-text-secondary">
              Start free — no credit card required. Upgrade anytime.
            </p>
          </div>

          <form class="space-y-3" @submit.prevent="handleCreateWorkspace">
            <div
              v-for="plan in planOptions"
              :key="plan.slug"
              class="border rounded-xl px-4 py-4 cursor-pointer transition-all duration-200 select-none"
              :class="
                form.plan === plan.slug
                  ? 'border-primary bg-primary/5 ring-2 ring-primary/20'
                  : 'border-border bg-surface hover:border-primary/30 hover:bg-surface-alt'
              "
              @click="form.plan = plan.slug"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                  <div
                    class="w-4 h-4 mt-0.5 rounded-full border-2 shrink-0 flex items-center justify-center transition-colors"
                    :class="
                      form.plan === plan.slug
                        ? 'border-primary'
                        : 'border-border'
                    "
                  >
                    <div
                      v-if="form.plan === plan.slug"
                      class="w-2 h-2 rounded-full bg-primary"
                    />
                  </div>
                  <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="text-sm font-bold text-text">{{
                        plan.name
                      }}</span>
                      <span
                        v-if="plan.badge"
                        class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                        :class="plan.badgeClass"
                        >{{ plan.badge }}</span
                      >
                    </div>
                    <p class="text-xs text-text-muted mt-0.5">
                      {{ plan.description }}
                    </p>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <span
                    v-if="plan.price === 0"
                    class="text-sm font-bold text-text"
                    >Free</span
                  >
                  <span v-else class="text-sm font-bold text-text"
                    >${{ plan.price
                    }}<span class="text-xs font-normal text-text-muted"
                      >/mo</span
                    ></span
                  >
                </div>
              </div>

              <!-- Features list -->
              <div class="mt-3 ml-7 flex flex-wrap gap-x-4 gap-y-1">
                <span
                  v-for="f in plan.features"
                  :key="f"
                  class="text-xs text-text-secondary flex items-center gap-1"
                >
                  <svg
                    class="w-3 h-3 text-success shrink-0"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2.5"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M4.5 12.75l6 6 9-13.5"
                    />
                  </svg>
                  {{ f }}
                </span>
              </div>
            </div>

            <!-- Error -->
            <div
              v-if="createError"
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
              <p class="text-sm text-danger">{{ createError }}</p>
            </div>

            <div class="flex gap-2 pt-2">
              <UiAppButton variant="outline" class="flex-1" @click="step = 2">
                Back
              </UiAppButton>
              <UiAppButton
                type="submit"
                class="flex-1"
                :loading="creating"
                :disabled="creating"
              >
                {{ creating ? "Creating workspace…" : "Get started →" }}
              </UiAppButton>
            </div>
          </form>
        </div>
      </Transition>

      <!-- Skip link — jumps to plan selection with free trial pre-selected -->
      <p v-if="step < totalSteps" class="text-center text-xs text-text-muted">
        <button class="hover:text-text transition-colors" @click="skipToCreate">
          Skip setup — start with free trial
        </button>
      </p>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: ["auth"],
});

// If the user already has a tenant, redirect to their workspace
const { user } = useAuth();
const { hasTenant } = useTenant();
const { goToWorkspace } = useWorkspaces();

if (user.value?.tenant_id && user.value?.tenant?.subdomain) {
  if (hasTenant.value) {
    // Already on the tenant subdomain, go to dashboard
    navigateTo("/dashboard", { replace: true });
  } else {
    // On root domain, redirect to tenant subdomain
    goToWorkspace(user.value.tenant.subdomain);
  }
}

const config = useRuntimeConfig();
const appDomain = config.public.appDomain as string;

const totalSteps = 3;
const step = ref(1);
const creating = ref(false);
const createError = ref("");

const form = reactive({
  businessName: "",
  industry: "",
  teamSize: "",
  role: "",
  plan: "free_trial" as string,
  currency: "USD",
});

const teamSizeOptions = [
  { value: "solo", label: "Just me", icon: "👤" },
  { value: "2-5", label: "2–5 people", icon: "👥" },
  { value: "6-20", label: "6–20 people", icon: "🏢" },
  { value: "20+", label: "20+ people", icon: "🏗️" },
];

const planOptions = [
  {
    slug: "free_trial",
    name: "Free Trial",
    price: 0,
    badge: "14 days free",
    badgeClass: "bg-success/10 text-success",
    description: "Try everything included in Starter. No credit card needed.",
    features: [
      "Up to 25 offers",
      "Up to 25 invoices",
      "Up to 25 products",
      "Up to 25 customers",
      "Up to 25 orders",
      "Payments & inventory",
    ],
  },
  {
    slug: "starter",
    name: "Starter",
    price: 19,
    badge: null,
    badgeClass: "",
    description: "Everything you need to run a small business.",
    features: [
      "Up to 250 offers",
      "Up to 250 invoices",
      "Up to 250 products",
      "Up to 250 customers",
      "Up to 250 orders",
      "Payments & inventory",
    ],
  },
  {
    slug: "pro",
    name: "Pro",
    price: 49,
    badge: "Most popular",
    badgeClass: "bg-primary/10 text-primary",
    description: "Unlimited records, plus storefront and analytics.",
    features: [
      "Everything in Starter — unlimited",
      "Online Storefront",
      "Reports & Analytics",
    ],
  },
];

/** Generate a URL-safe slug preview from the business name */
const slugPreview = computed(() => {
  return (
    form.businessName
      .trim()
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, "")
      .replace(/\s+/g, "-")
      .replace(/-+/g, "-")
      .slice(0, 30) || "your-business"
  );
});

function nextStep() {
  if (step.value < totalSteps) {
    step.value++;
  }
}

/** Skip the wizard steps and go straight to workspace creation */
function skipToCreate() {
  step.value = totalSteps;
}

const { authFetch } = useAuth();
const { fetchSubscription } = useSubscription();

async function handleCreateWorkspace() {
  creating.value = true;
  createError.value = "";

  try {
    const baseModules = ["invoices", "products", "customers"];
    const proModules = [...baseModules, "storefront", "reports"];
    const modules = form.plan === "pro" ? proModules : baseModules;

    const response = await authFetch<
      { subdomain: string } | { data: { subdomain: string } }
    >("/api/tenants", {
      method: "POST",
      body: {
        name: form.businessName.trim(),
        industry: form.industry || undefined,
        team_size: form.teamSize || undefined,
        role: form.role || undefined,
        plan: form.plan,
        modules,
        currency: form.currency,
      },
    });

    // Handle all response shapes:
    // { subdomain }, { data: { subdomain } }, { tenant: { subdomain } }, { user: { tenant: { subdomain } } }
    const subdomain =
      (response as any)?.subdomain ??
      (response as any)?.data?.subdomain ??
      (response as any)?.tenant?.subdomain ??
      (response as any)?.user?.tenant?.subdomain;

    if (subdomain) {
      // Fetch subscription now so the trial banner is ready on first dashboard load
      await fetchSubscription(authFetch);
      goToWorkspace(subdomain);
    } else {
      console.warn("[onboarding] No subdomain in response:", response);
      createError.value =
        "Workspace created but redirect failed. Please refresh and try logging in again.";
    }
  } catch (err: any) {
    if (err?.response?.status === 422) {
      const errors = err.response._data?.errors;
      createError.value =
        errors?.name?.[0] ||
        err.response._data?.message ||
        "Please check the workspace name.";
    } else if (err?.response?.status === 409) {
      createError.value =
        "A workspace with this name already exists. Please choose a different name.";
    } else {
      createError.value = "Unable to create workspace. Please try again.";
    }
  } finally {
    creating.value = false;
  }
}
</script>
