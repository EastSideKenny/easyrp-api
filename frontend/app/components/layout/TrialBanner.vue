<template>
  <div class="contents">
  <!-- Payment / billing attention (Stripe past_due, unpaid, incomplete) -->
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div
      v-if="needsBillingAttention"
      class="w-full px-4 py-2.5 flex items-center justify-between gap-4 text-sm font-medium bg-warning/10 border-b border-warning/25 text-warning"
    >
      <div class="flex items-center gap-2 min-w-0">
        <span class="shrink-0 text-base" aria-hidden="true">💳</span>
        <span class="truncate">{{ billingAttentionMessage }}</span>
      </div>
      <a
        href="/settings/billing"
        class="shrink-0 text-xs font-semibold px-3 py-1 rounded-lg bg-warning text-white hover:bg-warning/90 transition-colors duration-150 whitespace-nowrap"
      >
        Fix billing →
      </a>
    </div>
  </Transition>

  <!-- Paid plan canceled at period end — access continues until billing date -->
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div
      v-if="isCancelingAtPeriodEnd && !needsBillingAttention"
      class="w-full px-4 py-2.5 flex items-center justify-between gap-4 text-sm font-medium bg-primary/5 border-b border-primary/15 text-primary"
    >
      <div class="flex items-center gap-2 min-w-0">
        <span class="shrink-0 text-base" aria-hidden="true">📅</span>
        <span class="truncate">
          Your subscription will end on {{ cancelAccessEndsLabel }}. Until then you still have full access — you can resume billing anytime under Plan & Billing.
        </span>
      </div>
      <a
        href="/settings/billing"
        class="shrink-0 text-xs font-semibold px-3 py-1 rounded-lg bg-primary text-white hover:bg-primary/90 transition-colors duration-150 whitespace-nowrap"
      >
        Manage →
      </a>
    </div>
  </Transition>

  <!-- Canceled / paused — renew to use the app -->
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div
      v-if="needsSubscriptionRenewal && !needsBillingAttention"
      class="w-full px-4 py-2.5 flex items-center justify-between gap-4 text-sm font-medium bg-danger/10 border-b border-danger/20 text-danger"
    >
      <div class="flex items-center gap-2 min-w-0">
        <span class="shrink-0 text-base" aria-hidden="true">📋</span>
        <span class="truncate">
          Your subscription is no longer active. Choose a plan under Plan & Billing to keep using EasyRP.
        </span>
      </div>
      <a
        href="/settings/billing"
        class="shrink-0 text-xs font-semibold px-3 py-1 rounded-lg bg-danger text-white hover:bg-danger/90 transition-colors duration-150 whitespace-nowrap"
      >
        Renew →
      </a>
    </div>
  </Transition>

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
      v-if="isTrialing && !isExpired && !needsBillingAttention && !needsSubscriptionRenewal && !isCancelingAtPeriodEnd"
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
      v-if="isExpired && !needsBillingAttention && !needsSubscriptionRenewal && !isCancelingAtPeriodEnd"
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
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSubscription } from '../../composables/useSubscription'

const {
  subscription,
  isTrialing,
  isExpired,
  isUrgent,
  daysLeft,
  needsBillingAttention,
  needsSubscriptionRenewal,
  isCancelingAtPeriodEnd,
} = useSubscription()

const cancelAccessEndsLabel = computed(() => {
  const raw = subscription.value?.current_period_end
  if (!raw) return 'the end of your billing period'
  try {
    return new Date(raw).toLocaleDateString(undefined, {
      dateStyle: 'medium',
    })
  } catch {
    return 'the end of your billing period'
  }
})

const billingAttentionMessage = computed(() => {
  switch (subscription.value?.status) {
    case 'past_due':
      return 'Your last payment did not go through. Update your payment method to restore full access.'
    case 'unpaid':
      return 'There is an unpaid invoice on your account. Visit Plan & Billing to settle up and continue.'
    case 'incomplete':
      return 'Subscription checkout was not finished. Open Plan & Billing to complete payment.'
    default:
      return 'Billing needs your attention. Open Plan & Billing to continue without interruption.'
  }
})
</script>
