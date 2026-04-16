// ─── Core SaaS / Multi-Tenant Types ───

export interface Tenant {
    id: number
    name: string
    slug: string
    subdomain: string
    plan_id: number | null
    is_active: boolean
    created_at: string
    updated_at: string
    industry?: string | null
    team_size?: string | null
    modules?: string[] | null
    currency?: string | null
}

export type UserRole = 'owner' | 'admin' | 'staff'

export interface User {
    id: number
    tenant_id: number | null
    name: string
    email: string
    role: UserRole
    is_active: boolean
    is_site_admin: boolean
    last_login_at: string | null
    created_at: string
    updated_at: string
    tenant?: Tenant | null
}

// ─── Subscription & Feature Engine ───

/**
 * A single feature entry on a plan, including the per-tenant limit.
 * limit: null = unlimited (Pro plan)
 */
export interface PlanFeature {
    id: number
    code: string
    name: string
    description: string | null
    pivot: {
        limit: number | null
    }
}

export interface Plan {
    id: number
    name: string
    slug: string
    price_monthly: number
    price_yearly: number
    is_active: boolean
    features?: PlanFeature[]
    created_at: string
    updated_at: string
}

export interface Feature {
    id: number
    code: string
    name: string
    description: string | null
    created_at: string
    updated_at: string
}

export type SubscriptionStatus = 'active' | 'trialing' | 'expired' | 'canceled' | 'past_due'

/**
 * Per-feature usage entry returned inside GET /api/subscriptions.
 * remaining: null = unlimited
 */
export interface FeatureUsage {
    feature: string
    limit: number | null
    used: number
    remaining: number | null
}

/**
 * GET /api/subscriptions now returns a single object, not an array.
 * usage can be an array OR a plain object keyed by feature code.
 */
export interface TenantSubscription {
    id: number
    tenant_id: number
    plan_id: number
    plan?: Plan
    stripe_subscription_id: string | null
    status: SubscriptionStatus
    trial_ends_at: string | null
    current_period_end: string | null
    /** Per-feature usage — array OR object map keyed by feature code */
    usage?: FeatureUsage[] | Record<string, Omit<FeatureUsage, 'feature'>>
    created_at: string
    updated_at: string
}

// ─── Product System ───

export type ProductType = 'physical' | 'service'

export interface Product {
    id: number
    name: string
    description: string
    sku: string
    type: ProductType
    price: number
    cost_price: number
    tax_rate: number
    track_inventory: boolean
    stock_quantity: number
    low_stock_threshold: number
    is_active: boolean
    categories?: ProductCategory[]
    created_at: string
    updated_at: string
}

export interface ProductFormData {
    name: string
    sku: string
    description: string
    type: ProductType
    price: number
    cost_price: number
    tax_rate: number
    is_active: boolean
    track_inventory: boolean
    stock_quantity: number
    low_stock_threshold: number
    category_ids?: number[]
}

export interface ProductCategory {
    id: number
    name: string
    created_at: string
    updated_at: string
}

// ─── Customer (Basic CRM) ───

export interface Customer {
    id: number
    name: string
    email: string
    phone: string | null
    tax_number: string | null
    address_line_1: string | null
    address_line_2: string | null
    city: string | null
    postal_code: string | null
    country: string | null
    notes: string | null
    created_at: string
    updated_at: string
}

export interface CustomerFormData {
    name: string
    email: string
    phone: string | null
    tax_number: string | null
    address_line_1: string | null
    address_line_2: string | null
    city: string | null
    postal_code: string | null
    country: string | null
    notes: string | null
}

// ─── Invoicing System ───

export type InvoiceStatus = 'draft' | 'sent' | 'paid' | 'canceled'

export interface InvoiceItem {
    id?: number
    invoice_id?: number
    product_id: number | null
    description: string
    quantity: number
    unit_price: number
    tax_rate: number
    line_total: number
    product?: Product
    created_at?: string
    updated_at?: string
}

export interface Invoice {
    id: number
    invoice_number: string
    customer_id: number
    customer?: Customer
    order_id?: number | null
    order?: Order
    status: InvoiceStatus
    issue_date: string
    due_date: string
    subtotal: number
    tax_total: number
    total: number
    currency: string
    created_by: number | null
    items: InvoiceItem[]
    payments?: Payment[]
    created_at: string
    updated_at: string
}

export interface InvoiceFormData {
    customer_id: number | null
    order_id?: number | null
    status: InvoiceStatus
    issue_date: string
    due_date: string
    currency?: string
    items: Omit<InvoiceItem, 'id' | 'invoice_id' | 'product' | 'created_at' | 'updated_at'>[]
}

// ─── Offers ───

export type OfferStatus = 'draft' | 'sent' | 'accepted' | 'declined' | 'expired'

export interface OfferItem {
    id?: number
    offer_id?: number
    product_id: number | null
    description: string
    quantity: number
    unit_price: number
    tax_rate: number
    line_total: number
    product?: Product
    created_at?: string
    updated_at?: string
}

export interface Offer {
    id: number
    offer_number: string
    customer_id: number
    customer?: Customer
    status: OfferStatus
    issue_date: string
    valid_until: string
    subtotal: number
    tax_total: number
    total: number
    currency: string
    notes: string | null
    invoice_id: number | null
    invoice?: Invoice
    pdf_url: string | null
    created_by: number | null
    items: OfferItem[]
    created_at: string
    updated_at: string
}

export interface OfferFormData {
    customer_id: number | null
    issue_date: string
    valid_until: string
    currency?: string
    notes?: string
    items: Omit<OfferItem, 'id' | 'offer_id' | 'product' | 'created_at' | 'updated_at'>[]
}

// ─── Payments ───

export type PaymentMethod = 'cash' | 'bank' | 'stripe'

export interface Payment {
    id: number
    invoice_id: number
    invoice?: Invoice
    amount: number
    payment_method: PaymentMethod
    transaction_reference: string | null
    paid_at: string
    created_at: string
    updated_at: string
}

export interface PaymentFormData {
    invoice_id: number
    amount: number
    payment_method: PaymentMethod
    paid_at: string
    transaction_reference: string | null
}

// ─── Inventory / Stock Movements ───

export type StockMovementType = 'sale' | 'manual_adjustment'

export interface StockMovement {
    id: number
    product_id: number
    product?: Product
    product_name?: string
    type: StockMovementType
    quantity_change: number
    reference_id: number | null
    created_at: string
}

export interface StockMovementFormData {
    product_id: number
    type: StockMovementType
    quantity_change: number
    reference_id?: number | null
}

// ─── Webshop Types ───

export interface CartItem {
    product: Product
    quantity: number
}

export type OrderStatus = 'pending' | 'paid' | 'done' | 'canceled'

export interface Order {
    id: number
    order_number: string
    customer_id: number | null
    customer?: Customer
    status: OrderStatus
    subtotal: number
    tax_total: number
    total: number
    currency: string
    payment_status: string | null
    items: OrderItem[]
    invoices?: Invoice[]
    created_at: string
    updated_at: string
}

export interface OrderItem {
    id?: number
    order_id?: number
    product_id: number | null
    product?: Product
    description: string
    quantity: number
    unit_price: number
    tax_rate: number
    line_total: number
    created_at?: string
    updated_at?: string
}

export interface OrderFormData {
    customer_id: number | null
    status: OrderStatus
    items: OrderItemFormData[]
}

export interface OrderItemFormData {
    product_id: number | null
    description: string
    quantity: number
    unit_price: number
    tax_rate: number
    line_total: number
}

// ─── Webshop Settings ───

export interface WebshopSettings {
    id: number
    store_name: string | null
    primary_color: string | null
    currency: string
    enable_guest_checkout: boolean
    stripe_public_key: string | null
    // stripe_secret_key is never exposed to frontend
    created_at: string
    updated_at: string
}

// ─── Setup Wizard ───

export type SetupStep = 'business_info' | 'taxes' | 'product' | 'customer' | 'webshop'

export interface SetupProgress {
    id: number
    step: SetupStep
    is_completed: boolean
    created_at: string
    updated_at: string
}

// ─── Report Types ───

export interface RevenueReport {
    period: string
    revenue: number
    expenses: number
    profit: number
    invoice_count: number
    payment_count: number
}

export interface SalesByProductReport {
    product_id: number
    product_name: string
    sku: string
    units_sold: number
    revenue: number
    avg_price: number
}

export interface StockValueReport {
    product_id: number
    product_name: string
    sku: string
    stock_quantity: number
    cost_price: number
    stock_value: number
    retail_value: number
}

// ─── API Types ───

export interface PaginationMeta {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
}

/**
 * Laravel flat paginator format.
 * Pagination fields (current_page, last_page, etc.) sit alongside `data`.
 */
export interface PaginatedResponse<T> extends PaginationMeta {
    data: T[]
}

export interface ApiFilters {
    search?: string
    page?: number
    per_page?: number
    sort_by?: string
    sort_dir?: 'asc' | 'desc'
    [key: string]: string | number | boolean | undefined
}

// ─── UI Types ───

export type BadgeVariant = 'success' | 'warning' | 'danger' | 'info' | 'primary' | 'neutral'

export interface TableColumn {
    key: string
    label: string
    sortable?: boolean
    align?: 'left' | 'center' | 'right'
    class?: string
}

export interface NavItem {
    label: string
    icon: Component
    to: string
    badge?: string | number
}

import type { Component } from 'vue'

// ─── Webshop Settings ───

export interface WebshopSetting {
    id: number
    store_name: string
    primary_color: string
    currency: string
    enable_guest_checkout: boolean
    stripe_public_key: string | null
    stripe_secret_key?: string | null
    created_at: string
    updated_at: string
}

export interface WebshopSettingFormData {
    store_name: string
    primary_color: string
    currency: string
    enable_guest_checkout: boolean
    stripe_public_key: string
    stripe_secret_key: string
}

// ─── Nuxt Route Meta Extensions ───

declare module '#app' {
    interface PageMeta {
        /** Module slug required to access this page. Used by the module-guard middleware. */
        requiredModule?: string
    }
}
