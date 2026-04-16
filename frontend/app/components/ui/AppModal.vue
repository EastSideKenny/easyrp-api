<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="closeable && $emit('update:modelValue', false)"
      >
        <div class="absolute inset-0 bg-text/20 backdrop-blur-sm" />

        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-2"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-2"
        >
          <div
            v-if="modelValue"
            class="relative bg-surface rounded-2xl border border-border shadow-elevated w-full overflow-hidden"
            :class="sizeClass"
          >
            <div
              v-if="title || $slots.header"
              class="flex items-center justify-between px-6 py-4 border-b border-border"
            >
              <slot name="header">
                <h3 class="text-lg font-semibold text-text">{{ title }}</h3>
              </slot>
              <button
                v-if="closeable"
                class="text-text-muted hover:text-text p-1 rounded-lg hover:bg-surface-alt transition-colors"
                @click="$emit('update:modelValue', false)"
              >
                <svg
                  class="w-5 h-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>

            <div class="px-6 py-5">
              <slot />
            </div>

            <div
              v-if="$slots.footer"
              class="flex items-center justify-end gap-3 px-6 py-4 border-t border-border bg-surface-alt"
            >
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
type ModalSize = "sm" | "md" | "lg" | "xl" | "full";

const props = withDefaults(
  defineProps<{
    modelValue: boolean;
    title?: string;
    size?: ModalSize;
    closeable?: boolean;
  }>(),
  {
    size: "md",
    closeable: true,
  },
);

defineEmits<{
  "update:modelValue": [value: boolean];
}>();

const sizes: Record<ModalSize, string> = {
  sm: "max-w-sm",
  md: "max-w-lg",
  lg: "max-w-2xl",
  xl: "max-w-4xl",
  full: "max-w-6xl",
};

const sizeClass = computed(() => sizes[props.size]);
</script>
