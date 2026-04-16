<template>
  <div class="space-y-1.5">
    <label v-if="label" :for="id" class="block text-sm font-medium text-text">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <slot />
    <p v-if="error" class="text-xs text-danger mt-1">{{ error }}</p>
    <p v-else-if="hint" class="text-xs text-text-muted mt-1">{{ hint }}</p>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    label?: string;
    id?: string;
    error?: string;
    hint?: string;
    required?: boolean;
  }>(),
  {
    required: false,
  },
);
</script>
