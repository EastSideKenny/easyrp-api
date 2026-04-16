<template>
  <NuxtLayout name="landing">
    <!-- ─── Hero ─── -->
    <section
      class="relative overflow-hidden bg-grid pt-28 pb-20 sm:pt-36 sm:pb-28"
    >
      <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <UiAppBadge variant="primary" class="mb-6 animate-fade-in-up">
          Now in public beta — free to get started
        </UiAppBadge>

        <h1
          class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-text leading-[1.1] max-w-3xl mx-auto animate-fade-in-up stagger-1"
        >
          Run your entire business from
          <span
            class="bg-linear-to-r from-primary to-secondary bg-clip-text text-transparent"
            >one platform.</span
          >
        </h1>

        <p
          class="mt-6 text-lg sm:text-xl text-text-secondary max-w-xl mx-auto leading-relaxed animate-fade-in-up stagger-2"
        >
          Invoicing, inventory, payments, and a built-in online store — EasyRP
          gives you everything you need, without the complexity.
        </p>

        <div
          class="flex flex-col sm:flex-row justify-center gap-3 mt-8 animate-fade-in-up stagger-3"
        >
          <UiAppButton size="lg" to="/auth/register">
            Start for free →
          </UiAppButton>
          <UiAppButton size="lg" variant="outline" to="#demo">
            Watch demo
          </UiAppButton>
        </div>

        <p class="mt-4 text-xs text-text-muted animate-fade-in-up stagger-4">
          No credit card required · Free 14-day trial · Cancel anytime
        </p>

        <!-- Dashboard mockup -->
        <div class="mt-16 animate-fade-in-up stagger-4 relative">
          <div
            class="absolute -inset-4 bg-linear-to-b from-primary/5 to-transparent rounded-3xl blur-xl"
          />
          <UiAppCard :no-padding="true">
            <!-- Browser bar -->
            <template #header>
              <div class="flex items-center gap-2 w-full">
                <div class="flex gap-1.5">
                  <div class="w-3 h-3 rounded-full bg-danger/60" />
                  <div class="w-3 h-3 rounded-full bg-warning/60" />
                  <div class="w-3 h-3 rounded-full bg-success/60" />
                </div>
                <div class="flex-1 mx-4">
                  <div
                    class="bg-surface border border-border rounded-lg px-3 py-1 text-xs text-text-muted max-w-xs mx-auto text-center"
                  >
                    acme.easyrp.com/dashboard
                  </div>
                </div>
              </div>
            </template>
            <!-- Dashboard preview content -->
            <div class="p-6 space-y-4">
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div
                  v-for="stat in mockStats"
                  :key="stat.label"
                  class="bg-surface-alt border border-border rounded-xl p-4 text-left"
                >
                  <p class="text-xs text-text-muted">{{ stat.label }}</p>
                  <p class="text-lg font-bold text-text mt-1">
                    {{ stat.value }}
                  </p>
                  <p
                    class="text-xs mt-1"
                    :class="stat.up ? 'text-success' : 'text-danger'"
                  >
                    {{ stat.change }}
                  </p>
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div
                  class="md:col-span-2 bg-surface-alt border border-border rounded-xl p-4 h-32 flex items-end gap-1"
                >
                  <div
                    v-for="(h, i) in chartBars"
                    :key="i"
                    class="flex-1 bg-primary/20 rounded-t-md"
                    :style="`height: ${h}%`"
                  >
                    <div
                      class="w-full bg-primary rounded-t-md"
                      :style="`height: ${Math.min(h + 20, 100)}%`"
                    />
                  </div>
                </div>
                <div
                  class="bg-surface-alt border border-border rounded-xl p-4 space-y-2"
                >
                  <p class="text-xs font-medium text-text-muted">
                    Recent Activity
                  </p>
                  <div v-for="i in 3" :key="i" class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-primary/10 shrink-0" />
                    <div class="flex-1">
                      <div class="h-2 bg-border rounded-full w-full" />
                      <div class="h-2 bg-border rounded-full w-2/3 mt-1" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </UiAppCard>
        </div>
      </div>
    </section>

    <!-- ─── Logos / Social Proof ─── -->
    <section class="py-12 border-y border-border bg-surface">
      <div class="max-w-6xl mx-auto px-6">
        <p
          class="text-center text-xs font-semibold text-text-muted uppercase tracking-widest mb-8"
        >
          Trusted by growing businesses everywhere
        </p>
        <div
          class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6 opacity-40"
        >
          <div
            v-for="logo in logoNames"
            :key="logo"
            class="text-lg font-bold text-text tracking-tight"
          >
            {{ logo }}
          </div>
        </div>
      </div>
    </section>

    <!-- ─── Features ─── -->
    <section id="features" class="py-20 sm:py-28">
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <UiAppBadge variant="info" class="mb-4">
            All-in-one platform
          </UiAppBadge>
          <h2
            class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text"
          >
            Everything you need to run your business
          </h2>
          <p class="mt-4 text-text-secondary leading-relaxed">
            Stop juggling multiple tools. EasyRP brings invoicing, inventory,
            customer management, and your online storefront into one unified
            experience.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="feature in features"
            :key="feature.title"
            class="group bg-surface border border-border rounded-2xl p-6 space-y-3 hover:shadow-elevated hover:border-primary/20 transition-all duration-300"
          >
            <div class="flex items-start justify-between gap-2">
              <div
                class="w-11 h-11 rounded-xl flex items-center justify-center"
                :class="feature.iconBg"
              >
                <component
                  :is="feature.icon"
                  class="w-5 h-5"
                  :class="feature.iconColor"
                />
              </div>
              <span
                v-if="feature.plan"
                class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-primary/10 text-primary shrink-0 mt-1"
                >{{ feature.plan }}</span
              >
            </div>
            <h3 class="text-sm font-semibold text-text">
              {{ feature.title }}
            </h3>
            <p class="text-sm text-text-secondary leading-relaxed">
              {{ feature.desc }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── How It Works ─── -->
    <section class="py-20 sm:py-28 bg-surface border-y border-border">
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <UiAppBadge variant="success" class="mb-4"> Quick setup </UiAppBadge>
          <h2
            class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text"
          >
            Up and running in minutes
          </h2>
          <p class="mt-4 text-text-secondary leading-relaxed">
            No complex onboarding. Sign up, configure your workspace, and start
            managing your business right away.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div
            v-for="(step, i) in steps"
            :key="step.title"
            class="text-center relative"
          >
            <!-- Connector line -->
            <div
              v-if="i < steps.length - 1"
              class="hidden md:block absolute top-8 left-[60%] w-[80%] border-t border-dashed border-border"
            />
            <div
              class="w-16 h-16 rounded-2xl bg-primary/5 border border-primary/10 flex items-center justify-center mx-auto mb-5"
            >
              <component :is="step.icon" class="w-7 h-7 text-primary" />
            </div>
            <div
              class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary text-white text-xs font-bold mb-3"
            >
              {{ i + 1 }}
            </div>
            <h3 class="text-base font-semibold text-text mb-2">
              {{ step.title }}
            </h3>
            <p
              class="text-sm text-text-secondary leading-relaxed max-w-xs mx-auto"
            >
              {{ step.desc }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── Pricing ─── -->
    <section id="pricing" class="py-20 sm:py-28">
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <UiAppBadge variant="warning" class="mb-4">
            Simple pricing
          </UiAppBadge>
          <h2
            class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text"
          >
            Simple, transparent pricing
          </h2>
          <p class="mt-4 text-text-secondary leading-relaxed">
            Try everything free for 14 days. Pick the plan that fits when you're
            ready — no hidden fees, no per-seat charges.
          </p>

          <!-- Billing toggle -->
          <div class="flex items-center justify-center gap-3 mt-8">
            <button
              class="flex items-center gap-3 group"
              @click="yearly = !yearly"
            >
              <span
                class="text-sm transition-colors duration-200"
                :class="!yearly ? 'text-text font-semibold' : 'text-text-muted'"
                >Monthly</span
              >
              <div
                class="relative w-12 h-6 rounded-full bg-primary flex-shrink-0"
              >
                <span
                  class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                  :class="yearly ? 'translate-x-6' : 'translate-x-0'"
                />
              </div>
              <span
                class="text-sm transition-colors duration-200"
                :class="yearly ? 'text-text font-semibold' : 'text-text-muted'"
              >
                Yearly
                <span class="text-xs text-success font-semibold ml-1"
                  >Save 20%</span
                >
              </span>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-4xl mx-auto">
          <div
            v-for="plan in plans"
            :key="plan.name"
            class="relative bg-surface border rounded-2xl p-6 flex flex-col transition-all duration-300"
            :class="
              plan.popular
                ? 'border-primary shadow-elevated scale-[1.02]'
                : 'border-border hover:shadow-card'
            "
          >
            <div
              v-if="plan.popular"
              class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-2 bg-surface-alt"
            >
              <UiAppBadge variant="primary" :dot="false">
                Most popular
              </UiAppBadge>
            </div>
            <h3 class="text-base font-semibold text-text">{{ plan.name }}</h3>
            <p class="text-sm text-text-secondary mt-1 mb-4">
              {{ plan.desc }}
            </p>
            <div class="flex items-baseline gap-1 mb-6">
              <span
                v-if="(yearly ? plan.yearlyPrice : plan.monthlyPrice) === '0'"
                class="text-3xl font-extrabold text-text"
                >Free</span
              >
              <template v-else>
                <span class="text-3xl font-extrabold text-text">
                  €{{ yearly ? plan.yearlyPrice : plan.monthlyPrice }}
                </span>
                <span class="text-sm text-text-muted">{{
                  yearly ? "/yr" : "/mo"
                }}</span>
              </template>
            </div>
            <ul class="space-y-2.5 mb-8 flex-1">
              <li
                v-for="feat in plan.features"
                :key="feat"
                class="flex items-start gap-2 text-sm text-text-secondary"
              >
                <svg
                  class="w-4 h-4 text-success shrink-0 mt-0.5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2.5"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
                {{ feat }}
              </li>
            </ul>
            <UiAppButton
              :variant="plan.popular ? 'primary' : 'outline'"
              to="/auth/register"
              class="w-full"
            >
              {{ plan.cta }}
            </UiAppButton>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── Testimonials ─── -->
    <section
      id="testimonials"
      class="py-20 sm:py-28 bg-surface border-y border-border"
    >
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <UiAppBadge variant="info" class="mb-4">
            Customer stories
          </UiAppBadge>
          <h2
            class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text"
          >
            Loved by businesses like yours
          </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <div
            v-for="testimonial in testimonials"
            :key="testimonial.name"
            class="bg-surface-alt border border-border rounded-2xl p-6 flex flex-col"
          >
            <div class="flex gap-0.5 mb-4">
              <svg
                v-for="s in 5"
                :key="s"
                class="w-4 h-4 text-warning"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                />
              </svg>
            </div>
            <p class="text-sm text-text-secondary leading-relaxed flex-1 mb-5">
              "{{ testimonial.quote }}"
            </p>
            <div class="flex items-center gap-3 pt-4 border-t border-border">
              <div
                class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold"
                :class="testimonial.avatarBg"
              >
                {{ testimonial.initials }}
              </div>
              <div>
                <p class="text-sm font-medium text-text">
                  {{ testimonial.name }}
                </p>
                <p class="text-xs text-text-muted">{{ testimonial.role }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── FAQ ─── -->
    <section class="py-20 sm:py-28">
      <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-16">
          <h2
            class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text"
          >
            Frequently asked questions
          </h2>
          <p class="mt-4 text-text-secondary leading-relaxed">
            Can't find what you're looking for?
            <a href="#" class="text-primary hover:underline"
              >Reach out to our team</a
            >.
          </p>
        </div>

        <div class="space-y-3">
          <div
            v-for="(faq, i) in faqs"
            :key="i"
            class="bg-surface border border-border rounded-xl overflow-hidden"
          >
            <button
              class="w-full flex items-center justify-between px-6 py-4 text-left text-sm font-medium text-text hover:bg-surface-alt transition-colors"
              @click="openFaq = openFaq === i ? -1 : i"
            >
              {{ faq.q }}
              <svg
                class="w-4 h-4 text-text-muted shrink-0 transition-transform duration-200"
                :class="openFaq === i ? 'rotate-180' : ''"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="max-h-0 opacity-0"
              enter-to-class="max-h-40 opacity-100"
              leave-active-class="transition-all duration-150 ease-in"
              leave-from-class="max-h-40 opacity-100"
              leave-to-class="max-h-0 opacity-0"
            >
              <div v-show="openFaq === i" class="overflow-hidden">
                <p
                  class="px-6 pb-4 text-sm text-text-secondary leading-relaxed"
                >
                  {{ faq.a }}
                </p>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── Final CTA ─── -->
    <section class="py-20 sm:py-28">
      <div class="max-w-3xl mx-auto px-6 text-center">
        <div
          class="bg-linear-to-br from-text to-text/90 rounded-3xl px-8 py-16 sm:px-16 relative overflow-hidden"
        >
          <!-- Decorative blobs -->
          <div
            class="absolute -top-20 -right-20 w-60 h-60 bg-primary/20 rounded-full blur-3xl"
          />
          <div
            class="absolute -bottom-20 -left-20 w-60 h-60 bg-secondary/20 rounded-full blur-3xl"
          />

          <div class="relative z-10">
            <h2
              class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
            >
              Ready to simplify your business?
            </h2>
            <p class="mt-4 text-white/60 leading-relaxed max-w-md mx-auto">
              Join thousands of businesses already using EasyRP. Start your free
              trial today — no credit card required.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-3 mt-8">
              <NuxtLink
                to="/auth/register"
                class="bg-white text-text font-semibold px-7 py-3 rounded-xl hover:shadow-elevated transition-all duration-200 text-sm"
              >
                Get started for free →
              </NuxtLink>
              <a
                href="#"
                class="border border-white/20 text-white font-medium px-7 py-3 rounded-xl hover:bg-white/10 transition-all duration-200 text-sm"
              >
                Talk to sales
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </NuxtLayout>
</template>

<script setup lang="ts">
import {
  FileText,
  ShoppingCart,
  Package,
  Users,
  CreditCard,
  BarChart2,
  Zap,
  Settings,
  TrendingUp,
} from "lucide-vue-next";

definePageMeta({
  layout: false,
});

const yearly = ref(false);
const openFaq = ref(-1);

const mockStats = [
  {
    label: "Revenue",
    value: "$24,500",
    change: "↑ 12% vs last month",
    up: true,
  },
  {
    label: "Invoices",
    value: "38",
    change: "↑ 4 new this week",
    up: true,
  },
  {
    label: "Unpaid",
    value: "3",
    change: "↓ 1 from last month",
    up: false,
  },
  {
    label: "Clients",
    value: "14",
    change: "↑ 2 this month",
    up: true,
  },
];

const chartBars = [30, 45, 35, 55, 40, 65, 50, 70, 55, 80, 60, 75];

const logoNames = [
  "Riverview Co.",
  "Apex Studio",
  "Drift Supply",
  "Basecamp",
  "NorthStar",
  "Clearwater",
];

const features = [
  {
    icon: FileText,
    iconBg: "bg-primary/10",
    iconColor: "text-primary",
    title: "Professional Invoicing",
    desc: "Create, send, and track invoices in seconds. Automatic reminders and payment status tracking keep cash flow healthy.",
    plan: null,
  },
  {
    icon: Package,
    iconBg: "bg-warning/10",
    iconColor: "text-warning",
    title: "Inventory Management",
    desc: "Real-time stock tracking with low-stock alerts, adjustment history, and cost-of-goods insights.",
    plan: null,
  },
  {
    icon: Users,
    iconBg: "bg-success/10",
    iconColor: "text-success",
    title: "Customer Management",
    desc: "Organize contacts, track purchase history, and build stronger relationships with a complete customer profile.",
    plan: null,
  },
  {
    icon: CreditCard,
    iconBg: "bg-info/10",
    iconColor: "text-info",
    title: "Payments & Orders",
    desc: "Accept payments, create manual orders, and track what's owed — all in one place.",
    plan: null,
  },
  {
    icon: ShoppingCart,
    iconBg: "bg-secondary/10",
    iconColor: "text-secondary",
    title: "Online Storefront",
    desc: "Launch a beautiful, no-code storefront for your products. Accept orders 24/7. Available on Pro.",
    plan: "Pro",
  },
  {
    icon: BarChart2,
    iconBg: "bg-danger/10",
    iconColor: "text-danger",
    title: "Reports & Analytics",
    desc: "Revenue breakdowns, sales by product, stock valuation — real-time dashboards to make smarter decisions. Available on Pro.",
    plan: "Pro",
  },
];

const steps = [
  {
    icon: Zap,
    title: "Create your workspace",
    desc: "Sign up in 30 seconds. Get your own branded subdomain and a clean dashboard ready to go.",
  },
  {
    icon: Settings,
    title: "Add your products",
    desc: "Import or create your product catalog, set pricing, configure inventory tracking, and go live.",
  },
  {
    icon: TrendingUp,
    title: "Grow your business",
    desc: "Send invoices, accept payments, fulfill orders, and track everything from one unified platform.",
  },
];

const plans = [
  {
    name: "Free Trial",
    desc: "14 days, no card needed",
    monthlyPrice: "0",
    yearlyPrice: "0",
    popular: false,
    cta: "Start free trial",
    features: [
      "Up to 25 offers & invoices",
      "Up to 25 products & customers",
      "Up to 25 orders",
      "Payments & inventory (unlimited)",
      "14-day full access",
    ],
  },
  {
    name: "Starter",
    desc: "For small businesses",
    monthlyPrice: "19",
    yearlyPrice: "190",
    popular: false,
    cta: "Get started",
    features: [
      "Up to 250 offers & invoices",
      "Up to 250 products & customers",
      "Up to 250 orders",
      "Payments & inventory (unlimited)",
      "Email support",
    ],
  },
  {
    name: "Pro",
    desc: "For growing businesses",
    monthlyPrice: "49",
    yearlyPrice: "490",
    popular: true,
    cta: "Start free trial",
    features: [
      "Everything in Starter — unlimited",
      "Online Storefront",
      "Reports & analytics",
      "Priority support",
    ],
  },
];

const testimonials = [
  {
    name: "Sarah Chen",
    role: "Owner, Bloom Boutique",
    initials: "SC",
    avatarBg: "bg-primary/10 text-primary",
    quote:
      "We switched from QuickBooks and Shopify to EasyRP and cut our monthly tool costs in half. Everything just works together seamlessly.",
  },
  {
    name: "Marcus Williams",
    role: "Operations Lead, FreshBox",
    initials: "MW",
    avatarBg: "bg-secondary/10 text-secondary",
    quote:
      "The inventory management alone saved us hours each week. Add the invoicing and store on top, and it's a no-brainer for any small business.",
  },
  {
    name: "Elena Rodriguez",
    role: "Founder, Artisan Collective",
    initials: "ER",
    avatarBg: "bg-success/10 text-success",
    quote:
      "I'm not technical at all, but I had my online store live within an hour. The interface is incredibly clean and intuitive.",
  },
];

const faqs = [
  {
    q: "What's included in the free trial?",
    a: "The 14-day free trial gives you all Starter features — offers, invoices, products, inventory, customers, orders, and payments — up to 25 records each. No credit card required. When the trial ends, choose a paid plan to keep your data and lift the limits.",
  },
  {
    q: "What's the difference between Starter and Pro?",
    a: "Starter ($19/mo) covers core operations with limits of 250 records per feature. Pro ($49/mo) removes all record limits, adds the Online Storefront so customers can order directly, and unlocks Reports & Analytics for revenue insights and sales breakdowns.",
  },
  {
    q: "What happens when I hit a plan limit?",
    a: "You'll see a warning as you approach the limit (at 80% usage) and a clear error if you try to add more. Your existing data is safe — you just won't be able to create new records until you upgrade.",
  },
  {
    q: "Can I switch plans later?",
    a: "Absolutely. You can upgrade or downgrade at any time from your account settings. When upgrading, you'll be prorated for the remaining billing period.",
  },
  {
    q: "How does my workspace subdomain work?",
    a: "Each business gets its own isolated subdomain (e.g., yourcompany.easyrp.com). Your data is completely separate from other tenants and fully secure.",
  },
  {
    q: "What happens to my data when the trial expires?",
    a: "Your data is preserved. You'll just lose access to the app until you select a plan. We won't delete anything — upgrading immediately restores full access.",
  },
  {
    q: "Is my data secure?",
    a: "Yes. We use enterprise-grade encryption, automated backups, and strict tenant-level data isolation. Each workspace is fully sandboxed from others.",
  },
];
</script>
