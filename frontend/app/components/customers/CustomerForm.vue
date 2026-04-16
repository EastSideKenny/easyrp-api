<template>
  <form class="space-y-6" @submit.prevent="$emit('submit', form)">
    <div class="grid sm:grid-cols-2 gap-5">
      <UiAppFormField label="Full Name" :error="errors.name" required>
        <input
          v-model="form.name"
          type="text"
          placeholder="John Doe"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
        />
      </UiAppFormField>

      <UiAppFormField label="Email" :error="errors.email" required>
        <input
          v-model="form.email"
          type="email"
          placeholder="john@example.com"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
        />
      </UiAppFormField>

      <UiAppFormField label="Phone">
        <input
          v-model="form.phone"
          type="tel"
          placeholder="+1 555-0100"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
        />
      </UiAppFormField>

      <UiAppFormField label="Tax Number">
        <input
          v-model="form.tax_number"
          type="text"
          placeholder="EU123456789"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
        />
      </UiAppFormField>
    </div>

    <!-- Address Section -->
    <div>
      <p class="text-sm font-semibold text-text mb-3">Address</p>
      <div class="grid sm:grid-cols-2 gap-5">
        <UiAppFormField label="Address Line 1">
          <input
            v-model="form.address_line_1"
            type="text"
            placeholder="123 Main St"
            class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
          />
        </UiAppFormField>
        <UiAppFormField label="Address Line 2">
          <input
            v-model="form.address_line_2"
            type="text"
            placeholder="Suite 100"
            class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
          />
        </UiAppFormField>
        <UiAppFormField label="City">
          <input
            v-model="form.city"
            type="text"
            placeholder="New York"
            class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
          />
        </UiAppFormField>
        <UiAppFormField label="Postal Code">
          <input
            v-model="form.postal_code"
            type="text"
            placeholder="10001"
            class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
          />
        </UiAppFormField>
        <UiAppFormField label="Country">
          <input
            v-model="form.country"
            type="text"
            placeholder="United States"
            class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200"
          />
        </UiAppFormField>
      </div>
    </div>

    <UiAppFormField label="Notes">
      <textarea
        v-model="form.notes"
        rows="3"
        placeholder="Internal notes…"
        class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all duration-200 resize-none"
      />
    </UiAppFormField>

    <div
      class="flex items-center justify-end gap-3 pt-4 border-t border-border"
    >
      <UiAppButton variant="outline" @click="$emit('cancel')"
        >Cancel</UiAppButton
      >
      <UiAppButton type="submit" :loading="saving">{{
        submitLabel
      }}</UiAppButton>
    </div>
  </form>
</template>

<script setup lang="ts">
import type { CustomerFormData } from "~/types";

const props = withDefaults(
  defineProps<{
    initialData?: Partial<CustomerFormData>;
    saving?: boolean;
    submitLabel?: string;
    errors?: Record<string, string>;
  }>(),
  {
    saving: false,
    submitLabel: "Save Customer",
    errors: () => ({}),
  },
);

defineEmits<{
  submit: [data: CustomerFormData];
  cancel: [];
}>();

const form = reactive<CustomerFormData>({
  name: props.initialData?.name ?? "",
  email: props.initialData?.email ?? "",
  phone: props.initialData?.phone ?? null,
  tax_number: props.initialData?.tax_number ?? null,
  address_line_1: props.initialData?.address_line_1 ?? null,
  address_line_2: props.initialData?.address_line_2 ?? null,
  city: props.initialData?.city ?? null,
  postal_code: props.initialData?.postal_code ?? null,
  country: props.initialData?.country ?? null,
  notes: props.initialData?.notes ?? null,
});
</script>
