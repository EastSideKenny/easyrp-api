<template>
  <component
    :is="to ? 'NuxtLink' : 'button'"
    :to="to"
    :type="to ? undefined : type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
    :class="[sizeClasses, variantClasses]"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-0.5"
      :class="iconSizeClass"
      xmlns="http://www.w3.org/2000/svg"
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
    <slot />
  </component>
</template>

<script setup lang="ts">
type ButtonVariant =
  | "primary"
  | "secondary"
  | "outline"
  | "ghost"
  | "danger"
  | "success"
  | "dark";
type ButtonSize = "xs" | "sm" | "md" | "lg";

const props = withDefaults(
  defineProps<{
    variant?: ButtonVariant;
    size?: ButtonSize;
    to?: string;
    type?: "button" | "submit" | "reset";
    disabled?: boolean;
    loading?: boolean;
  }>(),
  {
    variant: "primary",
    size: "md",
    type: "button",
    disabled: false,
    loading: false,
  },
);

const sizeMap: Record<ButtonSize, string> = {
  xs: "text-xs px-2.5 py-1.5",
  sm: "text-sm px-3.5 py-2",
  md: "text-sm px-5 py-2.5",
  lg: "text-base px-6 py-3",
};

const variantMap: Record<ButtonVariant, string> = {
  primary:
    "bg-primary text-white hover:bg-primary-dark hover:shadow-elevated focus:ring-primary/20",
  secondary:
    "bg-secondary text-white hover:bg-secondary-dark hover:shadow-elevated focus:ring-secondary/20",
  outline:
    "bg-surface text-text border border-border hover:border-text-muted hover:shadow-card focus:ring-primary/10",
  ghost: "bg-primary/10 text-primary hover:bg-primary/20 focus:ring-primary/10",
  danger: "bg-danger text-white hover:opacity-90 focus:ring-danger/20",
  success: "bg-success text-white hover:opacity-90 focus:ring-success/20",
  dark: "bg-text text-white hover:opacity-90 focus:ring-text/20",
};

const iconSizeMap: Record<ButtonSize, string> = {
  xs: "w-3 h-3",
  sm: "w-3.5 h-3.5",
  md: "w-4 h-4",
  lg: "w-5 h-5",
};

const sizeClasses = computed(() => sizeMap[props.size]);
const variantClasses = computed(() => variantMap[props.variant]);
const iconSizeClass = computed(() => iconSizeMap[props.size]);
</script>
