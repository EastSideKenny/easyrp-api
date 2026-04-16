<template>
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
    <UiAppFormField label="From">
      <input
        v-model="from"
        type="date"
        class="border border-border rounded-xl px-4 py-2 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
      />
    </UiAppFormField>
    <UiAppFormField label="To">
      <input
        v-model="to"
        type="date"
        class="border border-border rounded-xl px-4 py-2 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
      />
    </UiAppFormField>
    <div class="flex gap-2 sm:self-end">
      <button
        v-for="preset in presets"
        :key="preset.label"
        class="text-xs font-medium px-3 py-2 rounded-lg transition-colors"
        :class="
          activePreset === preset.label
            ? 'bg-primary/10 text-primary'
            : 'text-text-muted hover:bg-surface-alt'
        "
        @click="applyPreset(preset)"
      >
        {{ preset.label }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
const from = defineModel<string>("from", { required: true });
const to = defineModel<string>("to", { required: true });

const activePreset = ref("");

interface Preset {
  label: string;
  days: number;
}

const presets: Preset[] = [
  { label: "7d", days: 7 },
  { label: "30d", days: 30 },
  { label: "90d", days: 90 },
  { label: "1y", days: 365 },
];

function applyPreset(preset: Preset) {
  activePreset.value = preset.label;
  const end = new Date();
  const start = new Date(end.getTime() - preset.days * 86400000);
  from.value = start.toISOString().slice(0, 10);
  to.value = end.toISOString().slice(0, 10);
}
</script>
