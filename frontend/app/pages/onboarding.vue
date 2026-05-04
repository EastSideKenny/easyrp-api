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
              Start free with a 14-day trial, or subscribe to Starter / Pro with a card — billing starts right away.
            </p>
          </div>

          <form class="space-y-3" @submit.prevent="handleCreateWorkspace">
            <div v-if="plansLoading" class="text-sm text-text-muted py-8 text-center">
              Loading plans…
            </div>
            <div
              v-else-if="!plans.length"
              class="rounded-xl border border-border bg-surface-alt px-4 py-6 text-sm text-text-secondary text-center space-y-2"
            >
              <p class="font-medium text-text">No plans are configured yet.</p>
              <p class="text-text-muted">
                On the API host, run
                <code class="text-xs bg-surface px-1.5 py-0.5 rounded border border-border"
                  >php artisan db:seed --class=PlanSeeder</code
                >
                (or migrate after pulling the latest migrations), then refresh this page.
              </p>
            </div>
            <template v-else>
              <SubscriptionsPlanSelector
                v-model="selectedPlanId"
                :billing-cycle="billingCycle"
                :plans="plans"
                :plans-loading="false"
                :current-plan-id="null"
                @update:billing-cycle="billingCycle = $event"
              />

              <div v-if="selectedPaidPlan" class="space-y-4 pt-2">
                <UiAppFormField label="Name on card" id="onboarding-card-name">
                  <input
                    id="onboarding-card-name"
                    v-model="cardholderName"
                    type="text"
                    autocomplete="cc-name"
                    class="w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                    placeholder="Full name"
                  />
                </UiAppFormField>
                <ClientOnly>
                  <SubscriptionsStripeCardForm
                    ref="stripeCardRef"
                    :disabled="creating"
                  />
                </ClientOnly>
              </div>
            </template>

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
                :disabled="creating || plansLoading || !selectedPlanId"
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
import type { User } from "~/types";

definePageMeta({
  layout: false,
  middleware: ["auth"],
});

// If the user already has a tenant, redirect to their workspace
const { user, authFetch } = useAuth();
const { stripeError } = useStripe();
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
const billingCycle = ref<"monthly" | "yearly">("monthly");
const selectedPlanId = ref<number | null>(null);
const cardholderName = ref("");
const stripeCardRef = ref<{
  getPaymentMethodId: (b?: { name?: string; email?: string }) => Promise<string | null>;
} | null>(null);

const form = reactive({
  businessName: "",
  industry: "",
  teamSize: "",
  role: "",
  currency: "USD",
});

const teamSizeOptions = [
  { value: "solo", label: "Just me", icon: "👤" },
  { value: "2-5", label: "2–5 people", icon: "👥" },
  { value: "6-20", label: "6–20 people", icon: "🏢" },
  { value: "20+", label: "20+ people", icon: "🏗️" },
];

const {
  plans,
  plansLoading,
  fetchPlans,
  fetchSubscription,
  subscribeToPaidPlan,
} = useSubscription();

const selectedPlan = computed(() =>
  plans.value.find((p) => p.id === selectedPlanId.value) ?? null,
);
const selectedPaidPlan = computed(
  () => selectedPlan.value && !selectedPlan.value.is_free,
);

onMounted(async () => {
  try {
    await fetchPlans(authFetch);
    if (plans.value.length) {
      const trial = plans.value.find((p) => p.slug === "free_trial");
      const first = plans.value[0];
      const pick = trial ?? first;
      if (pick) selectedPlanId.value = pick.id;
    }
  } catch {
    createError.value = "Could not load plans. Refresh and try again.";
  }
});

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

async function handleCreateWorkspace() {
  creating.value = true;
  createError.value = "";

  try {
    const plan = selectedPlan.value;
    if (!plan) {
      createError.value = "Select a plan to continue.";
      return;
    }

    let paymentMethodId: string | null = null;
    if (!plan.is_free) {
      paymentMethodId =
        (await stripeCardRef.value?.getPaymentMethodId({
          name: cardholderName.value.trim() || undefined,
          email: user.value?.email,
        })) ?? null;
      if (!paymentMethodId) {
        createError.value =
          stripeError.value || "Enter a valid card for the selected plan.";
        return;
      }
    }

    const baseModules = ["invoices", "products", "customers"];
    const proModules = [...baseModules, "storefront", "reports"];
    const modules = plan.slug === "pro" ? proModules : baseModules;

    const response = await authFetch<
      | { subdomain: string; user?: unknown; tenant?: unknown }
      | { data: { subdomain: string } }
    >("/api/tenants", {
      method: "POST",
      body: {
        name: form.businessName.trim(),
        industry: form.industry || undefined,
        team_size: form.teamSize || undefined,
        role: form.role || undefined,
        plan: plan.slug,
        modules,
        currency: form.currency,
      },
    });

    const createdUser = (response as { user?: User }).user;
    if (createdUser) {
      user.value = createdUser;
    }

    const subdomain =
      (response as any)?.subdomain ??
      (response as any)?.data?.subdomain ??
      (response as any)?.tenant?.subdomain ??
      (response as any)?.user?.tenant?.subdomain;

    if (subdomain) {
      if (paymentMethodId) {
        try {
          await subscribeToPaidPlan(
            plan.id,
            paymentMethodId,
            authFetch,
            billingCycle.value,
          );
        } catch (subErr: any) {
          createError.value =
            subErr?.data?.message ??
            subErr?.response?._data?.message ??
            "Workspace was created but billing failed. Open Plan & Billing in settings to add your card.";
          goToWorkspace(subdomain);
          return;
        }
      }
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
