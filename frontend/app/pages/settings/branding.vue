<template>
    <div class="max-w-3xl mx-auto space-y-8">
        <UiAppSectionHeader
            title="Branding & General"
            description="Customize your organization's name, theme, and logo."
        />

        <!-- Loading -->
        <UiAppCard v-if="loading" :no-padding="true">
            <div class="flex items-center justify-center py-16 text-text-muted">
                <svg
                    class="w-5 h-5 animate-spin mr-2"
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
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                    />
                </svg>
                Loading…
            </div>
        </UiAppCard>

        <form v-else class="space-y-6" @submit.prevent="handleSave">
            <!-- General -->
            <UiAppCard title="General">
                <div class="space-y-5">
                    <UiAppFormField label="Organization Name" required>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Acme Inc."
                            class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
                        />
                    </UiAppFormField>

                    <UiAppFormField label="Currency">
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
                            <option value="ZAR">
                                ZAR — South African Rand
                            </option>
                            <option value="BRL">BRL — Brazilian Real</option>
                            <option value="INR">INR — Indian Rupee</option>
                        </select>
                    </UiAppFormField>
                </div>
            </UiAppCard>

            <!-- Theme -->
            <UiAppCard title="Color Theme">
                <p class="text-sm text-text-muted mb-4">
                    Choose a color palette for your workspace.
                </p>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                    <button
                        v-for="t in themes"
                        :key="t.value"
                        type="button"
                        class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all duration-200"
                        :class="
                            form.theme === t.value
                                ? 'border-primary bg-primary/5'
                                : 'border-border hover:border-text-muted'
                        "
                        @click="form.theme = t.value"
                    >
                        <div
                            class="w-8 h-8 rounded-full"
                            :style="{ backgroundColor: t.swatch }"
                        />
                        <span class="text-xs font-medium text-text">{{
                            t.label
                        }}</span>
                    </button>
                </div>
            </UiAppCard>

            <!-- Logo -->
            <UiAppCard title="Logo">
                <div class="space-y-4">
                    <div v-if="logoUrl" class="flex items-center gap-4">
                        <img
                            :src="logoUrl"
                            alt="Logo"
                            class="h-16 w-auto rounded-lg border border-border object-contain bg-surface-alt p-1"
                        />
                        <UiAppButton
                            variant="danger"
                            size="sm"
                            :loading="deletingLogo"
                            @click="handleDeleteLogo"
                        >
                            Remove
                        </UiAppButton>
                    </div>
                    <div v-else class="text-sm text-text-muted">
                        No logo uploaded.
                    </div>

                    <UiAppFormField
                        label="Upload Logo"
                        hint="PNG, JPG, SVG or WebP. Max 2 MB."
                    >
                        <input
                            ref="logoInput"
                            type="file"
                            accept="image/png,image/jpeg,image/svg+xml,image/webp"
                            class="w-full text-sm text-text file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all"
                            @change="handleLogoUpload"
                        />
                    </UiAppFormField>
                </div>
            </UiAppCard>

            <!-- Save -->
            <div class="flex items-center justify-between">
                <p v-if="saved" class="text-sm text-success font-medium">
                    Settings saved successfully
                </p>
                <span v-else />
                <UiAppButton type="submit" :loading="saving"
                    >Save Settings</UiAppButton
                >
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
definePageMeta({
    layout: "default",
    middleware: ["auth", "tenant-admin"],
});

const toast = useToast();
const { tenant } = useTenant();
const { fetchBranding, updateBranding, uploadLogo, deleteLogo } = useSettings();

const loading = ref(true);
const saving = ref(false);
const deletingLogo = ref(false);
const saved = ref(false);
const logoUrl = ref<string | null>(null);
const logoInput = ref<HTMLInputElement | null>(null);

const themes = [
    { value: "default", label: "Default", swatch: "#6366f1" },
    { value: "blue", label: "Blue", swatch: "#3b82f6" },
    { value: "green", label: "Green", swatch: "#22c55e" },
    { value: "purple", label: "Purple", swatch: "#a855f7" },
    { value: "rose", label: "Rose", swatch: "#f43f5e" },
    { value: "orange", label: "Orange", swatch: "#f97316" },
];

const form = reactive({
    name: "",
    currency: "EUR",
    theme: "default",
});

onMounted(async () => {
    try {
        const data = await fetchBranding();
        form.name = data.name ?? "";
        form.currency = data.currency ?? "EUR";
        form.theme = data.theme ?? "default";
        logoUrl.value = data.logo_url;
    } catch {
        toast.error("Failed to load settings.");
    } finally {
        loading.value = false;
    }
});

async function handleSave() {
    saving.value = true;
    saved.value = false;
    try {
        const data = await updateBranding({
            name: form.name,
            currency: form.currency,
            theme: form.theme,
        });
        logoUrl.value = data.logo_url;
        // Sync tenant state so theme plugin and sidebar react immediately
        if (tenant.value) {
            tenant.value = {
                ...tenant.value,
                name: data.name,
                currency: data.currency,
                theme: data.theme,
                logo_url: data.logo_url,
            };
        }
        saved.value = true;
        toast.success("Settings saved.");
        setTimeout(() => (saved.value = false), 3000);
    } catch {
        toast.error("Failed to save settings.");
    } finally {
        saving.value = false;
    }
}

async function handleLogoUpload(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    try {
        const data = await uploadLogo(file);
        logoUrl.value = data.logo_url;
        if (tenant.value)
            tenant.value = { ...tenant.value, logo_url: data.logo_url };
        toast.success("Logo uploaded.");
    } catch {
        toast.error("Failed to upload logo.");
    }
    if (logoInput.value) logoInput.value.value = "";
}

async function handleDeleteLogo() {
    deletingLogo.value = true;
    try {
        await deleteLogo();
        logoUrl.value = null;
        if (tenant.value) tenant.value = { ...tenant.value, logo_url: null };
        toast.success("Logo removed.");
    } catch {
        toast.error("Failed to remove logo.");
    } finally {
        deletingLogo.value = false;
    }
}
</script>
