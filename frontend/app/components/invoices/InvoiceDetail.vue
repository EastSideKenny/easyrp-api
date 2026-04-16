<template>
  <div v-if="invoice" class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <h2 class="text-xl font-bold text-text">
          {{ invoice.invoice_number }}
        </h2>
        <p class="text-sm text-text-muted">
          Issued {{ new Date(invoice.issue_date).toLocaleDateString() }} · Due
          {{ new Date(invoice.due_date).toLocaleDateString() }}
        </p>
      </div>
      <div class="flex items-center gap-1.5">
        <UiAppBadge :variant="statusVariant(invoice.status)">{{
          invoice.status
        }}</UiAppBadge>
        <UiAppButton
          v-if="invoice.status === 'draft'"
          variant="primary"
          size="xs"
          @click="$emit('send')"
        >
          <Send class="w-3.5 h-3.5" /> Send Invoice
        </UiAppButton>
        <UiAppButton
          v-if="invoice.status !== 'paid' && balanceDue > 0"
          variant="success"
          size="xs"
          @click="$emit('record-payment')"
        >
          <CreditCard class="w-3.5 h-3.5" /> Record Payment
        </UiAppButton>
        <UiAppButton
          v-if="invoice.status !== 'draft'"
          variant="ghost"
          size="xs"
          @click="$emit('download')"
        >
          <Download class="w-3.5 h-3.5" /> Download PDF
        </UiAppButton>
        <UiAppButton
          v-if="invoice.status === 'draft' || invoice.status === 'canceled'"
          variant="ghost"
          size="xs"
          @click="$emit('edit')"
        >
          <Pencil class="w-3.5 h-3.5" /> Edit
        </UiAppButton>
        <UiAppButton
          v-if="invoice.status === 'draft' || invoice.status === 'canceled'"
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
      <UiAppStatCard label="Total" :value="formatCurrency(invoice.total)" />
      <UiAppStatCard label="Paid" :value="formatCurrency(totalPaid)" />
      <UiAppStatCard
        label="Balance Due"
        :value="formatCurrency(balanceDue)"
        :class="
          balanceDue > 0 ? 'ring-1 ring-warning/30' : 'ring-1 ring-success/30'
        "
      />
      <UiAppStatCard label="Customer" :value="invoice.customer?.name ?? '—'" />
    </div>

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
            v-for="item in invoice.items"
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
              {{ formatCurrency(invoice.subtotal) }}
            </td>
          </tr>
          <tr>
            <td colspan="4" class="px-6 py-1 text-right text-text-muted">
              Tax
            </td>
            <td class="px-6 py-1 text-right tabular-nums">
              {{ formatCurrency(invoice.tax_total) }}
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
              {{ formatCurrency(invoice.total) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </UiAppCard>

    <!-- Payments -->
    <UiAppCard title="Payments" :no-padding="true">
      <table v-if="invoice.payments?.length" class="w-full text-sm">
        <thead>
          <tr class="border-b border-border bg-surface-alt">
            <th
              class="text-left px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Date
            </th>
            <th
              class="text-left px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Method
            </th>
            <th
              class="text-left px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Reference
            </th>
            <th
              class="text-right px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Amount
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="payment in invoice.payments"
            :key="payment.id"
            class="border-b border-border-light last:border-0"
          >
            <td class="px-6 py-3 text-text">
              {{ new Date(payment.paid_at).toLocaleDateString() }}
            </td>
            <td class="px-6 py-3 text-text capitalize">
              {{ payment.payment_method }}
            </td>
            <td class="px-6 py-3 text-text-muted">
              {{ payment.transaction_reference ?? "—" }}
            </td>
            <td
              class="px-6 py-3 text-right font-medium text-success tabular-nums"
            >
              {{ formatCurrency(payment.amount) }}
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="border-t border-border">
            <td
              colspan="3"
              class="px-6 py-3 text-right font-semibold text-text"
            >
              Balance Due
            </td>
            <td
              class="px-6 py-3 text-right font-bold tabular-nums"
              :class="balanceDue > 0 ? 'text-warning' : 'text-success'"
            >
              {{ formatCurrency(balanceDue) }}
            </td>
          </tr>
        </tfoot>
      </table>
      <div v-else class="py-8 text-center text-sm text-text-muted">
        No payments recorded yet.
      </div>
    </UiAppCard>
  </div>
</template>

<script setup lang="ts">
import type { Invoice, InvoiceStatus, BadgeVariant } from "~/types";
import { Send, CreditCard, Pencil, Trash2, Download } from "lucide-vue-next";

const props = defineProps<{
  invoice: Invoice | null;
}>();

defineEmits<{
  send: [];
  "record-payment": [];
  edit: [];
  delete: [];
  download: [];
}>();

const { formatCurrency } = useCurrency();

const totalPaid = computed(() =>
  (props.invoice?.payments ?? []).reduce((sum, p) => sum + Number(p.amount), 0),
);

const balanceDue = computed(() =>
  Math.max(0, Number(props.invoice?.total ?? 0) - totalPaid.value),
);

function statusVariant(status: InvoiceStatus): BadgeVariant {
  const map: Record<InvoiceStatus, BadgeVariant> = {
    draft: "info",
    sent: "primary",
    paid: "success",
    canceled: "neutral",
  };
  return map[status];
}
</script>
