<template>
  <div v-if="order" class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <h2 class="text-xl font-bold text-text">
          {{ order.order_number }}
        </h2>
        <p class="text-sm text-text-muted">
          Placed {{ new Date(order.created_at).toLocaleDateString() }}
        </p>
      </div>
      <div class="flex items-center gap-1.5">
        <UiAppBadge :variant="statusVariant(order.status)">{{
          order.status
        }}</UiAppBadge>
        <UiAppBadge
          v-if="order.payment_status"
          :variant="paymentStatusVariant(order.payment_status)"
        >
          {{ order.payment_status }}
        </UiAppBadge>
        <UiAppButton
          v-if="order.status === 'pending'"
          variant="ghost"
          size="xs"
          @click="$emit('edit')"
        >
          <Pencil class="w-3.5 h-3.5" /> Edit
        </UiAppButton>
        <UiAppButton
          v-if="order.status === 'pending'"
          variant="success"
          size="xs"
          @click="$emit('mark-done')"
        >
          <CheckCircle class="w-3.5 h-3.5" /> Mark as Done
        </UiAppButton>
        <UiAppButton
          v-if="order.status === 'done'"
          variant="primary"
          size="xs"
          @click="$emit('create-invoice')"
        >
          <FileText class="w-3.5 h-3.5" /> Create Invoice
        </UiAppButton>
        <UiAppButton
          v-if="order.status === 'pending'"
          variant="ghost"
          size="xs"
          class="text-danger! hover:bg-danger/10!"
          @click="$emit('cancel-order')"
        >
          <XCircle class="w-3.5 h-3.5" /> Cancel
        </UiAppButton>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid sm:grid-cols-4 gap-4">
      <UiAppStatCard
        label="Total"
        :value="formatCurrency(Number(order.total))"
      />
      <UiAppStatCard
        label="Subtotal"
        :value="formatCurrency(Number(order.subtotal))"
      />
      <UiAppStatCard
        label="Tax"
        :value="formatCurrency(Number(order.tax_total))"
      />
      <UiAppStatCard label="Customer" :value="order.customer?.name ?? '—'" />
    </div>

    <!-- Line Items -->
    <UiAppCard title="Items" :no-padding="true">
      <table v-if="order.items?.length" class="w-full text-sm">
        <thead>
          <tr class="border-b border-border bg-surface-alt">
            <th
              class="text-left px-6 py-3 text-xs font-semibold text-text-muted uppercase tracking-wider"
            >
              Product
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
            v-for="item in order.items"
            :key="item.id"
            class="border-b border-border-light last:border-0"
          >
            <td class="px-6 py-3 text-text">
              <span>{{ item.product?.name ?? item.description ?? "—" }}</span>
              <span
                v-if="item.product && item.description"
                class="block text-xs text-text-muted"
              >
                {{ item.description }}
              </span>
            </td>
            <td class="px-6 py-3 text-right tabular-nums">
              {{ item.quantity }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums">
              {{ formatCurrency(Number(item.unit_price)) }}
            </td>
            <td class="px-6 py-3 text-right tabular-nums text-text-muted">
              {{ item.tax_rate }}%
            </td>
            <td class="px-6 py-3 text-right font-medium tabular-nums">
              {{ formatCurrency(Number(item.line_total)) }}
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="border-t border-border">
            <td colspan="4" class="px-6 py-3 text-right text-text-muted">
              Subtotal
            </td>
            <td class="px-6 py-3 text-right font-medium tabular-nums">
              {{ formatCurrency(Number(order.subtotal)) }}
            </td>
          </tr>
          <tr>
            <td colspan="4" class="px-6 py-1 text-right text-text-muted">
              Tax
            </td>
            <td class="px-6 py-1 text-right tabular-nums">
              {{ formatCurrency(Number(order.tax_total)) }}
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
              {{ formatCurrency(Number(order.total)) }}
            </td>
          </tr>
        </tfoot>
      </table>
      <div v-else class="py-8 text-center text-sm text-text-muted">
        No items on this order.
      </div>
    </UiAppCard>

    <!-- Invoices -->
    <UiAppCard
      v-if="order.invoices !== undefined"
      title="Invoices"
      :no-padding="true"
    >
      <div v-if="order.invoices?.length" class="divide-y divide-border">
        <div v-for="inv in order.invoices" :key="inv.id">
          <!-- Invoice summary row -->
          <div
            class="flex items-center gap-4 px-6 py-3 hover:bg-surface-alt transition-colors cursor-pointer"
            @click="navigateTo(`/invoices/${inv.id}`)"
          >
            <span class="font-semibold text-primary text-sm">{{
              inv.invoice_number
            }}</span>
            <span class="text-sm text-text-muted">
              {{ new Date(inv.issue_date).toLocaleDateString() }} –
              {{ new Date(inv.due_date).toLocaleDateString() }}
            </span>
            <span class="text-sm font-medium tabular-nums ml-auto">{{
              formatCurrency(Number(inv.total))
            }}</span>
            <UiAppBadge :variant="invoiceStatusVariant(inv.status)">{{
              inv.status
            }}</UiAppBadge>
          </div>
        </div>
      </div>
      <div v-else class="py-8 text-center text-sm text-text-muted">
        No invoices for this order.
      </div>
    </UiAppCard>
  </div>
</template>

<script setup lang="ts">
import type { Order, OrderStatus, InvoiceStatus, BadgeVariant } from "~/types";
import { Pencil, CheckCircle, FileText, XCircle } from "lucide-vue-next";

defineProps<{
  order: Order | null;
}>();

defineEmits<{
  edit: [];
  "mark-done": [];
  "mark-paid": [];
  "cancel-order": [];
  "create-invoice": [];
}>();

const { formatCurrency } = useCurrency();

function statusVariant(status: OrderStatus): BadgeVariant {
  const map: Record<OrderStatus, BadgeVariant> = {
    pending: "warning",
    paid: "success",
    done: "info",
    canceled: "danger",
  };
  return map[status];
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

function paymentStatusVariant(status: string): BadgeVariant {
  const map: Record<string, BadgeVariant> = {
    pending: "warning",
    paid: "success",
    failed: "danger",
    refunded: "info",
  };
  return map[status] ?? "neutral";
}
</script>
