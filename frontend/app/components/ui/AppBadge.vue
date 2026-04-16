<template>
  <span
    class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg"
    :class="variantClasses"
  >
    <span v-if="dot" class="w-1.5 h-1.5 rounded-full" :class="dotClass" />
    <slot />
  </span>
</template>

<script setup lang="ts">
import type { BadgeVariant } from '~/types'

const props = withDefaults(defineProps<{
  variant?: BadgeVariant
  dot?: boolean
}>(), {
  variant: 'neutral',
  dot: true,
})

const variants: Record<BadgeVariant, string> = {
  success: 'bg-success/10 text-success',
  warning: 'bg-warning/10 text-warning',
  danger: 'bg-danger/10 text-danger',
  info: 'bg-info/10 text-info',
  primary: 'bg-primary/10 text-primary',
  neutral: 'bg-surface-alt text-text-muted border border-border',
}

const dots: Record<BadgeVariant, string> = {
  success: 'bg-success',
  warning: 'bg-warning',
  danger: 'bg-danger',
  info: 'bg-info',
  primary: 'bg-primary',
  neutral: 'bg-text-muted',
}

const variantClasses = computed(() => variants[props.variant])
const dotClass = computed(() => dots[props.variant])
</script>
