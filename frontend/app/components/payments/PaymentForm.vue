<template>
  <form class="space-y-6" @submit.prevent="$emit('submit', form)">
    <div class="grid sm:grid-cols-2 gap-5">
      <UiAppFormField label="Invoice" :error="errors.invoice_id" required>
        <select
          v-model.number="form.invoice_id"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
          @change="onInvoiceChange"
        >
          <option :value="0" disabled>Select invoice…</option>
          <option v-for="inv in unpaidInvoices" :key="inv.id" :value="inv.id">
            {{ inv.invoice_number }} — {{ inv.customer?.name }} ({{
              formatCurrency(invoiceBalance(inv))
            }}
            remaining)
          </option>
          <option value="__new__">+ New Invoice…</option>
        </select>
      </UiAppFormField>

      <UiAppFormField label="Amount" :error="errors.amount" required>
        <div class="relative">
          <span
            class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-text-muted"
            >{{ currencySymbol }}</span
          >
          <input
            v-model.number="form.amount"
            type="number"
            step="0.01"
            min="0"
            :max="balanceDue || undefined"
            placeholder="0.00"
            class="w-full border border-border rounded-xl pl-8 pr-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
          />
        </div>
        <p v-if="selectedInvoice" class="mt-1 text-xs text-text-muted">
          Balance due:
          <span
            class="font-semibold"
            :class="balanceDue > 0 ? 'text-warning' : 'text-success'"
            >{{ formatCurrency(balanceDue) }}</span
          >
        </p>
      </UiAppFormField>

      <UiAppFormField label="Payment Method" required>
        <select
          v-model="form.payment_method"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
        >
          <option value="cash">Cash</option>
          <option value="bank">Bank</option>
          <option value="stripe">Stripe</option>
        </select>
      </UiAppFormField>

      <UiAppFormField label="Paid At" required>
        <input
          v-model="form.paid_at"
          type="date"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
        />
      </UiAppFormField>

      <UiAppFormField label="Transaction Reference">
        <input
          v-model="form.transaction_reference"
          type="text"
          placeholder="e.g. CHK-1234"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text placeholder-text-muted bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
        />
      </UiAppFormField>
    </div>

    <div
      class="flex items-center justify-end gap-3 pt-4 border-t border-border"
    >
      <UiAppButton variant="outline" @click="$emit('cancel')"
        >Cancel</UiAppButton
      >
      <UiAppButton type="submit" :loading="saving">Record Payment</UiAppButton>
    </div>
  </form>

  <!-- Quick-create Invoice modal -->
  <UiAppModal v-model="showNewInvoice" title="New Invoice" size="xl">
    <InvoicesInvoiceForm
      :customers="customers"
      :products="products"
      :saving="savingInvoice"
      :errors="invoiceErrors"
      submit-label="Create Invoice"
      @submit="handleQuickCreateInvoice"
      @cancel="showNewInvoice = false"
    />
  </UiAppModal>
</template>

<script setup lang="ts">
import type {
  Invoice,
  InvoiceFormData,
  Customer,
  Product,
  PaymentFormData,
} from "~/types";

const props = withDefaults(
  defineProps<{
    invoices: Invoice[];
    customers?: Customer[];
    products?: Product[];
    preselectedInvoiceId?: number;
    saving?: boolean;
    errors?: Record<string, string>;
  }>(),
  {
    customers: () => [],
    products: () => [],
    saving: false,
    errors: () => ({}),
  },
);

const emit = defineEmits<{
  submit: [data: PaymentFormData];
  cancel: [];
  "invoice-created": [invoice: Invoice];
}>();

const { formatCurrency, tenantCurrency } = useCurrency();
const currencySymbol = computed(() =>
  (0)
    .toLocaleString("en", {
      style: "currency",
      currency: tenantCurrency.value,
      minimumFractionDigits: 0,
    })
    .replace(/[\d.,\s]/g, "")
    .trim(),
);

// Helper: balance remaining on an invoice
function invoiceBalance(inv: Invoice): number {
  const paid = (inv.payments ?? []).reduce(
    (sum, p) => sum + Number(p.amount),
    0,
  );
  return Math.max(0, Number(inv.total) - paid);
}

// Only show invoices that still have a balance due
const unpaidInvoices = computed(() =>
  props.invoices.filter(
    (inv) => inv.status !== "paid" && invoiceBalance(inv) > 0,
  ),
);

// Calculate remaining balance for the selected invoice
const selectedInvoice = computed(() =>
  props.invoices.find((inv) => inv.id === form.invoice_id),
);

const totalPaid = computed(() =>
  (selectedInvoice.value?.payments ?? []).reduce(
    (sum, p) => sum + Number(p.amount),
    0,
  ),
);

const balanceDue = computed(() =>
  Math.max(0, Number(selectedInvoice.value?.total ?? 0) - totalPaid.value),
);

// --- Quick-create Invoice ---
const showNewInvoice = ref(false);
const savingInvoice = ref(false);
const invoiceErrors = ref<Record<string, string>>({});
const { createInvoice } = useInvoices();
const toast = useToast();

function onInvoiceChange(e: Event) {
  const val = (e.target as HTMLSelectElement).value;
  if (val === "__new__") {
    form.invoice_id = 0;
    showNewInvoice.value = true;
  }
}

async function handleQuickCreateInvoice(
  data: InvoiceFormData & {
    subtotal: number;
    tax_total: number;
    total: number;
  },
) {
  savingInvoice.value = true;
  invoiceErrors.value = {};
  try {
    const invoice = await createInvoice(data);
    emit("invoice-created", invoice);
    form.invoice_id = invoice.id;
    form.amount = Math.round(Number(invoice.total) * 100) / 100;
    showNewInvoice.value = false;
  } catch (e: any) {
    if (e?.data?.errors) invoiceErrors.value = e.data.errors;
    toast.apiError(e, "Failed to create invoice.");
  } finally {
    savingInvoice.value = false;
  }
}

const form = reactive<PaymentFormData>({
  invoice_id: props.preselectedInvoiceId ?? 0,
  amount: 0,
  payment_method: "cash",
  paid_at: new Date().toISOString().slice(0, 10),
  transaction_reference: null,
});

// Pre-fill amount with balance due when invoice is selected or pre-selected
watch(
  () => form.invoice_id,
  () => {
    if (balanceDue.value > 0) {
      form.amount = Math.round(balanceDue.value * 100) / 100;
    }
  },
  { immediate: true },
);
</script>
