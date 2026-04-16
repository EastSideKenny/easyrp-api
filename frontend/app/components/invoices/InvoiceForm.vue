<template>
  <form class="space-y-6" @submit.prevent="handleSubmit">
    <div class="grid sm:grid-cols-2 gap-5">
      <UiAppFormField label="Customer" :error="errors.customer_id" required>
        <select
          v-model.number="form.customer_id"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
          @change="onCustomerChange"
        >
          <option :value="null" disabled>Select customer…</option>
          <option v-for="c in localCustomers" :key="c.id" :value="c.id">
            {{ c.name }}
          </option>
          <option value="__new__">+ New Customer…</option>
        </select>
      </UiAppFormField>

      <UiAppFormField label="Status">
        <select
          v-model="form.status"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
        >
          <option value="draft">Draft</option>
          <option value="sent">Sent</option>
          <option value="paid">Paid</option>
          <option value="canceled">Canceled</option>
        </select>
      </UiAppFormField>

      <UiAppFormField label="Issue Date" :error="errors.issue_date" required>
        <input
          v-model="form.issue_date"
          type="date"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
        />
      </UiAppFormField>

      <UiAppFormField label="Due Date" :error="errors.due_date" required>
        <input
          v-model="form.due_date"
          type="date"
          class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
        />
      </UiAppFormField>
    </div>

    <!-- Line Items -->
    <UiAppCard title="Items" :no-padding="false">
      <InvoicesInvoiceLineItems
        ref="lineItemsRef"
        v-model="form.items"
        :products="localProducts"
        @new-product="openNewProduct"
      />
    </UiAppCard>

    <div
      class="flex items-center justify-end gap-3 pt-4 border-t border-border"
    >
      <UiAppButton variant="outline" @click="$emit('cancel')"
        >Cancel</UiAppButton
      >
      <UiAppButton type="submit" :loading="saving">{{
        submitLabel
      }}</UiAppButton>
    </div>
  </form>

  <!-- Quick-create Customer modal -->
  <UiAppModal v-model="showNewCustomer" title="New Customer" size="lg">
    <CustomersCustomerForm
      :saving="savingCustomer"
      :errors="customerErrors"
      submit-label="Create Customer"
      @submit="handleQuickCreateCustomer"
      @cancel="showNewCustomer = false"
    />
  </UiAppModal>

  <!-- Quick-create Product modal -->
  <UiAppModal v-model="showNewProduct" title="New Product" size="lg">
    <ProductsProductForm
      :saving="savingProduct"
      :errors="productErrors"
      submit-label="Create Product"
      @submit="handleQuickCreateProduct"
      @cancel="showNewProduct = false"
    />
  </UiAppModal>
</template>

<script setup lang="ts">
import type {
  Customer,
  CustomerFormData,
  Product,
  ProductFormData,
  InvoiceFormData,
} from "~/types";

const props = withDefaults(
  defineProps<{
    customers: Customer[];
    products?: Product[];
    initialData?: Partial<InvoiceFormData>;
    saving?: boolean;
    submitLabel?: string;
    errors?: Record<string, string>;
  }>(),
  {
    saving: false,
    submitLabel: "Save Invoice",
    errors: () => ({}),
  },
);

const emit = defineEmits<{
  submit: [
    data: InvoiceFormData & {
      subtotal: number;
      tax_total: number;
      total: number;
    },
  ];
  cancel: [];
}>();

const lineItemsRef = ref<{
  subtotal: number;
  taxTotal: number;
  total: number;
}>();

const today = new Date().toISOString().slice(0, 10);
const dueDate = new Date(Date.now() + 30 * 86400000).toISOString().slice(0, 10);

const form = reactive<InvoiceFormData>({
  customer_id: props.initialData?.customer_id ?? null,
  status: props.initialData?.status ?? "draft",
  issue_date: props.initialData?.issue_date ?? today,
  due_date: props.initialData?.due_date ?? dueDate,
  items: props.initialData?.items ?? [],
});

// Local copies so we can append newly created records without needing a page reload
const localCustomers = ref<Customer[]>([...props.customers]);
const localProducts = ref<Product[]>([...(props.products ?? [])]);

// Keep in sync if the parent re-fetches and passes updated lists
watch(
  () => props.customers,
  (v) => {
    localCustomers.value = [...v];
  },
);
watch(
  () => props.products,
  (v) => {
    localProducts.value = [...(v ?? [])];
  },
);

const toast = useToast();

// --- Quick-create Customer ---
const showNewCustomer = ref(false);
const savingCustomer = ref(false);
const customerErrors = ref<Record<string, string>>({});
const { createCustomer } = useCustomers();

function onCustomerChange(e: Event) {
  const val = (e.target as HTMLSelectElement).value;
  if (val === "__new__") {
    form.customer_id = null;
    showNewCustomer.value = true;
  }
}

async function handleQuickCreateCustomer(data: CustomerFormData) {
  savingCustomer.value = true;
  customerErrors.value = {};
  try {
    const customer = await createCustomer(data);
    localCustomers.value.push(customer);
    form.customer_id = customer.id;
    showNewCustomer.value = false;
  } catch (e: any) {
    if (e?.data?.errors) customerErrors.value = e.data.errors;
    toast.apiError(e, "Failed to create customer.");
  } finally {
    savingCustomer.value = false;
  }
}

// --- Quick-create Product ---
const showNewProduct = ref(false);
const savingProduct = ref(false);
const productErrors = ref<Record<string, string>>({});
const pendingProductLineIdx = ref<number | null>(null);
const { createProduct } = useProducts();

function openNewProduct(idx: number) {
  pendingProductLineIdx.value = idx;
  showNewProduct.value = true;
}

async function handleQuickCreateProduct(data: ProductFormData) {
  savingProduct.value = true;
  productErrors.value = {};
  try {
    const product = await createProduct(data);
    localProducts.value.push(product);
    // Auto-fill the line item that triggered the modal
    const idx = pendingProductLineIdx.value;
    if (idx !== null && form.items[idx] !== undefined) {
      const line = form.items[idx];
      line.product_id = product.id;
      line.description = product.name;
      line.unit_price = Number(product.price);
      line.tax_rate = Number(product.tax_rate);
    }
    showNewProduct.value = false;
    pendingProductLineIdx.value = null;
  } catch (e: any) {
    if (e?.data?.errors) productErrors.value = e.data.errors;
    toast.apiError(e, "Failed to create product.");
  } finally {
    savingProduct.value = false;
  }
}

function handleSubmit() {
  emit("submit", {
    ...form,
    subtotal: lineItemsRef.value?.subtotal ?? 0,
    tax_total: lineItemsRef.value?.taxTotal ?? 0,
    total: lineItemsRef.value?.total ?? 0,
  });
}
</script>
