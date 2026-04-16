<template>
  <div v-if="payment" class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <h2 class="text-xl font-bold text-text">Payment #{{ payment.id }}</h2>
        <p class="text-sm text-text-muted">
          Recorded {{ new Date(payment.created_at).toLocaleDateString() }}
        </p>
      </div>
      <div class="flex items-center gap-1.5">
        <UiAppBadge :variant="methodVariant(payment.payment_method)">
          {{ formatMethod(payment.payment_method) }}
        </UiAppBadge>
        <UiAppButton
          variant="ghost"
          size="xs"
          @click="navigateTo(`/invoices/${payment.invoice_id}`)"
        >
          <FileText class="w-3.5 h-3.5" /> View Invoice
        </UiAppButton>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid sm:grid-cols-3 gap-4">
      <UiAppStatCard
        label="Amount"
        :value="formatCurrency(Number(payment.amount))"
      />
      <UiAppStatCard
        label="Paid On"
        :value="new Date(payment.paid_at).toLocaleDateString()"
      />
      <UiAppStatCard
        label="Reference"
        :value="payment.transaction_reference ?? '—'"
      />
    </div>

    <!-- Invoice details -->
    <UiAppCard title="Invoice" :no-padding="true">
      <div
        v-if="payment.invoice"
        class="flex items-center gap-4 px-6 py-4 hover:bg-surface-alt transition-colors cursor-pointer"
        @click="navigateTo(`/invoices/${payment.invoice_id}`)"
      >
        <div class="flex-1">
          <p class="font-semibold text-primary text-sm">
            {{ payment.invoice.invoice_number }}
          </p>
          <p class="text-xs text-text-muted mt-0.5">
            {{ payment.invoice.customer?.name ?? "—" }} · Due
            {{ new Date(payment.invoice.due_date).toLocaleDateString() }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-sm font-medium tabular-nums">{{
            formatCurrency(Number(payment.invoice.total))
          }}</span>
          <UiAppBadge :variant="invoiceStatusVariant(payment.invoice.status)">{{
            payment.invoice.status
          }}</UiAppBadge>
        </div>
        <span class="text-text-muted text-sm">→</span>
      </div>
      <div v-else class="px-6 py-4 text-sm text-text-muted">
        Invoice #{{ payment.invoice_id }}
        <button
          type="button"
          class="ml-2 text-primary hover:underline text-xs"
          @click="navigateTo(`/invoices/${payment.invoice_id}`)"
        >
          View →
        </button>
      </div>
    </UiAppCard>
  </div>
</template>

<script setup lang="ts">
import type {
  Payment,
  PaymentMethod,
  InvoiceStatus,
  BadgeVariant,
} from "~/types";
import { FileText } from "lucide-vue-next";

defineProps<{
  payment: Payment | null;
}>();

defineEmits<{
  delete: [];
}>();

const { formatCurrency } = useCurrency();

function formatMethod(method: PaymentMethod): string {
  const map: Record<PaymentMethod, string> = {
    cash: "Cash",
    bank: "Bank Transfer",
    stripe: "Stripe",
  };
  return map[method] ?? method;
}

function methodVariant(method: PaymentMethod): BadgeVariant {
  const map: Record<PaymentMethod, BadgeVariant> = {
    cash: "neutral",
    bank: "info",
    stripe: "primary",
  };
  return map[method] ?? "neutral";
}

function invoiceStatusVariant(status: InvoiceStatus): BadgeVariant {
  const map: Record<InvoiceStatus, BadgeVariant> = {
    draft: "neutral",
    sent: "info",
    paid: "success",
    canceled: "danger",
  };
  return map[status];
}
</script>
