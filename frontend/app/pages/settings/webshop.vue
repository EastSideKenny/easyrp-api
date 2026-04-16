<template>
  <div class="max-w-3xl mx-auto space-y-8">
    <UiAppSectionHeader
      title="Webshop Settings"
      description="Configure your online storefront appearance, currency, checkout options, and payment integration."
    />

    <!-- Loading state -->
    <UiAppCard v-if="loading" :no-padding="true">
      <div class="flex items-center justify-center py-16 text-text-muted">
        <svg class="w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
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
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
          />
        </svg>
        Loading settings…
      </div>
    </UiAppCard>

    <form v-else class="space-y-6" @submit.prevent="handleSave">
      <!-- General -->
      <UiAppCard title="General">
        <div class="space-y-5">
          <UiAppFormField
            label="Store Name"
            :error="errors.store_name"
            required
          >
            <input
              v-model="form.store_name"
              type="text"
              placeholder="e.g. My Awesome Shop"
              class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
            />
          </UiAppFormField>

          <div class="grid sm:grid-cols-2 gap-5">
            <UiAppFormField
              label="Primary Color"
              :error="errors.primary_color"
              required
            >
              <div class="flex items-center gap-3">
                <input
                  v-model="form.primary_color"
                  type="color"
                  class="w-10 h-10 rounded-lg border border-border cursor-pointer p-0.5"
                />
                <input
                  v-model="form.primary_color"
                  type="text"
                  placeholder="#4f46e5"
                  class="flex-1 border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200 font-mono"
                />
              </div>
            </UiAppFormField>

            <UiAppFormField label="Currency" :error="errors.currency" required>
              <select
                v-model="form.currency"
                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200 appearance-none"
              >
                <option value="USD">USD — US Dollar</option>
                <option value="EUR">EUR — Euro</option>
                <option value="GBP">GBP — British Pound</option>
                <option value="CAD">CAD — Canadian Dollar</option>
                <option value="AUD">AUD — Australian Dollar</option>
                <option value="JPY">JPY — Japanese Yen</option>
                <option value="CHF">CHF — Swiss Franc</option>
                <option value="SEK">SEK — Swedish Krona</option>
                <option value="NOK">NOK — Norwegian Krone</option>
                <option value="DKK">DKK — Danish Krone</option>
                <option value="ZAR">ZAR — South African Rand</option>
                <option value="BRL">BRL — Brazilian Real</option>
                <option value="INR">INR — Indian Rupee</option>
              </select>
            </UiAppFormField>
          </div>
        </div>
      </UiAppCard>

      <!-- Checkout -->
      <UiAppCard title="Checkout">
        <div class="space-y-4">
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              v-model="form.enable_guest_checkout"
              type="checkbox"
              class="w-4 h-4 rounded border-border text-primary focus:ring-primary/20"
            />
            <div>
              <p class="text-sm font-medium text-text">Enable guest checkout</p>
              <p class="text-xs text-text-muted">
                Allow customers to place orders without creating an account.
              </p>
            </div>
          </label>
        </div>
      </UiAppCard>

      <!-- Stripe -->
      <UiAppCard title="Payment — Stripe">
        <div class="space-y-5">
          <p class="text-sm text-text-muted -mt-1">
            Connect your Stripe account to accept online payments. Find your
            keys in the
            <a
              href="https://dashboard.stripe.com/apikeys"
              target="_blank"
              class="text-primary hover:underline"
              >Stripe Dashboard</a
            >.
          </p>

          <UiAppFormField
            label="Publishable Key"
            :error="errors.stripe_public_key"
            hint="Starts with pk_live_ or pk_test_"
          >
            <input
              v-model="form.stripe_public_key"
              type="text"
              placeholder="pk_test_…"
              class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200 font-mono"
            />
          </UiAppFormField>

          <UiAppFormField
            label="Secret Key"
            :error="errors.stripe_secret_key"
            hint="Starts with sk_live_ or sk_test_. This value is never returned by the API."
          >
            <input
              v-model="form.stripe_secret_key"
              type="password"
              placeholder="sk_test_…"
              autocomplete="off"
              class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200 font-mono"
            />
            <p
              v-if="settings?.stripe_public_key && !form.stripe_secret_key"
              class="text-xs text-text-muted mt-1"
            >
              Leave blank to keep the existing secret key unchanged.
            </p>
          </UiAppFormField>
        </div>
      </UiAppCard>

      <!-- Actions -->
      <div class="flex items-center justify-between">
        <p v-if="saved" class="text-sm text-success font-medium">
          ✓ Settings saved successfully
        </p>
        <span v-else />
        <UiAppButton type="submit" :loading="saving">Save Settings</UiAppButton>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import type { WebshopSettingFormData } from "~/types";

definePageMeta({
  layout: "default",
  middleware: [
    "auth",
    function () {
      const { tenant } = useTenant();
      const modules = tenant.value?.modules;
      if (modules && modules.length > 0 && !modules.includes("storefront")) {
        return navigateTo("/dashboard");
      }
    },
  ],
});

const { settings, loading, saving, errors, fetchSettings, updateSettings } =
  useWebshopSettings();

const saved = ref(false);

const form = reactive<WebshopSettingFormData>({
  store_name: "",
  primary_color: "#4f46e5",
  currency: "USD",
  enable_guest_checkout: false,
  stripe_public_key: "",
  stripe_secret_key: "",
});

onMounted(async () => {
  await fetchSettings();
  if (settings.value) {
    form.store_name = settings.value.store_name ?? "";
    form.primary_color = settings.value.primary_color ?? "#4f46e5";
    form.currency = settings.value.currency ?? "USD";
    form.enable_guest_checkout = settings.value.enable_guest_checkout ?? false;
    form.stripe_public_key = settings.value.stripe_public_key ?? "";
    // secret key is hidden by backend — leave blank
    form.stripe_secret_key = "";
  }
});

async function handleSave() {
  saved.value = false;
  const toast = useToast();

  // If the user didn't enter a new secret key, don't send it
  const payload: WebshopSettingFormData = { ...toRaw(form) };
  if (!payload.stripe_secret_key) {
    delete (payload as any).stripe_secret_key;
  }

  const ok = await updateSettings(payload);
  if (ok) {
    saved.value = true;
    toast.success("Settings saved successfully.");
    setTimeout(() => (saved.value = false), 3000);
  } else {
    toast.error("Failed to save settings. Please check the form.");
  }
}
</script>
