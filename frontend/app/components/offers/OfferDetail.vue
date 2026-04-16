<template>
  <div v-if="offer" class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <h2 class="text-xl font-bold text-text">
          {{ offer.offer_number }}
        </h2>
        <p class="text-sm text-text-muted">
          Issued {{ new Date(offer.issue_date).toLocaleDateString() }} · Valid
          until {{ new Date(offer.valid_until).toLocaleDateString() }}
        </p>
        <button
          v-if="offer.invoice_id"
          type="button"
          class="mt-1 text-xs text-primary hover:underline"
          @click="navigateTo(`/invoices/${offer.invoice_id}`)"
        >
          View linked invoice
          {{
            offer.invoice?.invoice_number
              ? `(${offer.invoice.invoice_number})`
              : ""
          }}
          →
        </button>
      </div>
      <div class="flex items-center gap-1.5">
        <UiAppBadge :variant="statusVariant(offer.status)">{{
          offer.status
        }}</UiAppBadge>
        <UiAppButton
          v-if="offer.status === 'draft'"
          variant="primary"
          size="xs"
          @click="$emit('send')"
        >
          <Send class="w-3.5 h-3.5" /> Send Offer
        </UiAppButton>
        <UiAppButton
          v-if="offer.status === 'sent' || offer.status === 'draft'"
          variant="success"
          size="xs"
          @click="$emit('accept')"
        >
          <CheckCircle class="w-3.5 h-3.5" /> Accept &amp; Invoice
        </UiAppButton>
        <UiAppButton
          v-if="offer.status === 'sent' || offer.status === 'draft'"
          variant="ghost"
          size="xs"
          class="text-danger! hover:bg-danger/10!"
          @click="$emit('decline')"
        >
          <XCircle class="w-3.5 h-3.5" /> Decline
        </UiAppButton>
        <UiAppButton variant="ghost" size="xs" @click="$emit('download-pdf')">
          <Download class="w-3.5 h-3.5" /> PDF
        </UiAppButton>
        <UiAppButton
          v-if="offer.status !== 'accepted'"
          variant="ghost"
          size="xs"
          @click="$emit('edit')"
        >
          <Pencil class="w-3.5 h-3.5" /> Edit
        </UiAppButton>
        <UiAppButton
          v-if="offer.status !== 'accepted'"
          variant="ghost"
          size="xs"
          class="text-danger! hover:bg-danger/10!"
          @click="$emit('delete')"
        >
          <Trash2 class="w-3.5 h-3.5" /> Delete
        </UiAppButton>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid sm:grid-cols-4 gap-4">
      <UiAppStatCard label="Total" :value="formatCurrency(offer.total)" />
      <UiAppStatCard label="Customer" :value="offer.customer?.name ?? '—'" />
      <UiAppStatCard
        label="Valid Until"
        :value="new Date(offer.valid_until).toLocaleDateString()"
        :class="isExpired ? 'ring-1 ring-danger/30' : 'ring-1 ring-success/30'"
      />
      <UiAppStatCard
        label="Invoice"
        :value="offer.invoice?.invoice_number ?? 'Not yet created'"
      />
    </div>

    <!-- Notes -->
    <UiAppCard v-if="offer.notes" title="Notes">
      <p class="text-sm text-text-muted whitespace-pre-line">
        {{ offer.notes }}
      </p>
    </UiAppCard>

    <!-- Line Items -->
    <UiAppCard title="Items" :no-padding="true">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-border bg-surface-alt">
            <th
              class="text-left px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Description
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Qty
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Price
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Tax
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Total
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in offer.items"
            :key="item.id"
            class="border-b border-border-light last:border-0"
          >
            <td class="px-6 py-3 text-text">{{ item.description }}</td>
            <td class="px-6 py-3 text-right tabular-nums">
              {{ item.quantity }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums">
              {{ formatCurrency(item.unit_price) }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums text-text-muted">
              {{ item.tax_rate }}%
            </td>
            <td class="px-6 py-3 text-right font-medium tabular-nums">
              {{ formatCurrency(item.line_total) }}
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="border-t border-border">
            <td colspan="4" class="px-6 py-3 text-right text-text-muted">
              Subtotal
            </td>
            <td class="px-6 py-3 text-right font-medium tabular-nums">
              {{ formatCurrency(offer.subtotal) }}
            </td>
          </tr>
          <tr>
            <td colspan="4" class="px-6 py-1 text-right text-text-muted">
              Tax
            </td>
            <td class="px-6 py-1 text-right tabular-nums">
              {{ formatCurrency(offer.tax_total) }}
            </td>
          </tr>
          <tr class="border-t border-border">
            <td
              colspan="4"
              class="px-6 py-3 text-right font-semibold text-text"
            >
              Total
            </td>
            <td
              class="px-6 py-3 text-right font-bold text-text text-base tabular-nums"
            >
              {{ formatCurrency(offer.total) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </UiAppCard>
  </div>
</template>

<script setup lang="ts">
import type { Offer, OfferStatus, BadgeVariant } from "~/types";
import {
  Send,
  CheckCircle,
  XCircle,
  Download,
  Pencil,
  Trash2,
} from "lucide-vue-next";

const props = defineProps<{
  offer: Offer | null;
}>();

defineEmits<{
  send: [];
  accept: [];
  decline: [];
  "download-pdf": [];
  edit: [];
  delete: [];
}>();

const { formatCurrency } = useCurrency();

const isExpired = computed(() => {
  if (!props.offer) return false;
  return new Date(props.offer.valid_until) < new Date();
});

function statusVariant(status: OfferStatus): BadgeVariant {
  const map: Record<OfferStatus, BadgeVariant> = {
    draft: "info",
    sent: "primary",
    accepted: "success",
    declined: "danger",
    expired: "neutral",
  };
  return map[status];
}
</script>
