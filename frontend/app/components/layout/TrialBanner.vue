<template>
  <!-- Active trial banner -->
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div
      v-if="isTrialing && !isExpired"
      :class="[
        'w-full px-4 py-2.5 flex items-center justify-between gap-4 text-sm font-medium',
        isUrgent
          ? 'bg-danger/10 border-b border-danger/20 text-danger'
          : 'bg-primary/10 border-b border-primary/20 text-primary',
      ]"
    >
      <div class="flex items-center gap-2 min-w-0">
        <span class="shrink-0 text-base" aria-hidden="true">🕒</span>
        <span class="truncate">
          <span v-if="daysLeft === 1">1 day left in your free trial.</span>
          <span v-else>{{ daysLeft }} days left in your free trial.</span>
        </span>
      </div>
      <a
        href="/settings/billing"
        :class="[
          'shrink-0 text-xs font-semibold px-3 py-1 rounded-lg transition-colors duration-150 whitespace-nowrap',
          isUrgent
            ? 'bg-danger text-white hover:bg-danger/90'
            : 'bg-primary text-white hover:bg-primary/90',
        ]"
      >
        Upgrade now →
      </a>
    </div>
  </Transition>

  <!-- Expired banner -->
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
  >
    <div
      v-if="isExpired"
      class="w-full px-4 py-2.5 flex items-center justify-between gap-4 text-sm font-medium bg-danger/10 border-b border-danger/20 text-danger"
    >
      <div class="flex items-center gap-2 min-w-0">
        <span class="shrink-0 text-base" aria-hidden="true">⚠️</span>
        <span class="truncate">
          Your free trial has expired. Upgrade to keep using EasyRP.
        </span>
      </div>
      <a
        href="/settings/billing"
        class="shrink-0 text-xs font-semibold px-3 py-1 rounded-lg bg-danger text-white hover:bg-danger/90 transition-colors duration-150 whitespace-nowrap"
      >
        Upgrade now →
      </a>
    </div>
  </Transition>
</template>

<script setup lang="ts">
const { isTrialing, isExpired, isUrgent, daysLeft } = useSubscription();
</script>
