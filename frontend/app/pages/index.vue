<template>
    <NuxtLayout name="landing">
        <section class="pt-24 pb-20 sm:pt-30 sm:pb-24">
            <div class="max-w-6xl mx-auto px-6">
                <div class="marketing-card-neo rounded-3xl p-6 sm:p-10 lg:p-12 relative overflow-hidden">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_15%_0%,rgba(79,70,229,0.12),transparent_40%),radial-gradient(circle_at_85%_10%,rgba(16,185,129,0.1),transparent_35%)] pointer-events-none" />
                    <div class="relative">
                        <UiAppBadge variant="primary" class="animate-fade-in-up">
                            Quote-to-invoice automation - free to start
                        </UiAppBadge>
                        <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.03] tracking-tight text-text max-w-4xl animate-fade-in-up stagger-1">
                            Client asks for a quote.
                            <span class="text-primary">You send it in minutes.</span>
                            They accept before lunch.
                        </h1>
                        <p class="mt-5 text-lg text-text-secondary max-w-2xl leading-relaxed animate-fade-in-up stagger-2">
                            Built for fast operators: chat-like quoting flow, instant customer delivery,
                            and invoice generation that matches exactly what was approved.
                        </p>

                        <div class="mt-8 flex flex-col sm:flex-row gap-3 animate-fade-in-up stagger-3">
                            <NuxtLink
                                to="/auth/register"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold marketing-cta hover:shadow-elevated transition-all"
                            >
                                Start free →
                            </NuxtLink>
                            <a
                                href="mailto:support@easy-rp.com"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold bg-white border border-border hover:border-primary/30 transition-all"
                            >
                                Talk to us
                            </a>
                        </div>

                        <div class="mt-10 grid lg:grid-cols-12 gap-6 items-start">
                            <div class="lg:col-span-5 rounded-2xl border border-border bg-white p-4 sm:p-5 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-text-muted mb-3">
                                    Conversation simulation
                                </p>
                                <div class="space-y-3 text-sm">
                                    <div class="max-w-[92%] rounded-2xl rounded-bl-md bg-surface-alt border border-border px-3 py-2 animate-fade-in-up">
                                        Hi! Can you send me a quote for 40 custom mugs?
                                    </div>
                                    <div class="max-w-[92%] ml-auto rounded-2xl rounded-br-md bg-primary text-white px-3 py-2 animate-fade-in-up stagger-1">
                                        Absolutely. I just sent Offer #OR-1042. Check your email.
                                    </div>
                                    <div class="max-w-[92%] rounded-2xl rounded-bl-md bg-surface-alt border border-border px-3 py-2 animate-fade-in-up stagger-2">
                                        Wow that was fast. Accepted it - let's get to work.
                                    </div>
                                    <div class="flex items-center gap-2 text-text-muted animate-pulse mt-1">
                                        <span class="w-2 h-2 rounded-full bg-text-muted/50" />
                                        <span class="w-2 h-2 rounded-full bg-text-muted/50" />
                                        <span class="w-2 h-2 rounded-full bg-text-muted/50" />
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-7 rounded-2xl border border-border bg-white shadow-sm overflow-hidden">
                                <div class="grid md:grid-cols-12 min-h-[320px]">
                                    <aside class="md:col-span-3 border-r border-border bg-surface-alt p-3">
                                        <p class="text-xs font-semibold text-text mb-3">Acme</p>
                                        <nav class="space-y-1">
                                            <button
                                                v-for="item in dashboardSidebarItems"
                                                :key="item"
                                                type="button"
                                                class="w-full text-left text-[11px] rounded-md px-2 py-1.5 transition-colors"
                                                :class="item === 'Dashboard' ? 'bg-primary/10 text-primary font-semibold' : 'text-text-secondary hover:bg-white'"
                                            >
                                                {{ item }}
                                            </button>
                                        </nav>
                                    </aside>
                                    <div class="md:col-span-9 p-3 sm:p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h3 class="text-sm font-semibold text-text">Good afternoon, John</h3>
                                            <span class="text-[11px] text-primary font-semibold">View all</span>
                                        </div>

                                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                                            <div
                                                v-for="stat in mockStats"
                                                :key="stat.label"
                                                class="rounded-lg border border-border bg-white p-2.5"
                                            >
                                                <p class="text-[10px] uppercase tracking-wide text-text-muted">{{ stat.label }}</p>
                                                <p class="mt-1 text-base font-bold text-text">{{ stat.value }}</p>
                                                <p class="text-[10px] text-text-muted">this month</p>
                                            </div>
                                        </div>

                                        <div class="mt-3 rounded-lg border border-border overflow-hidden">
                                            <div class="px-3 py-2 bg-surface-alt border-b border-border text-xs font-semibold text-text">
                                                Recent Invoices
                                            </div>
                                            <table class="w-full text-[11px]">
                                                <thead class="bg-white text-text-muted border-b border-border">
                                                    <tr>
                                                        <th class="text-left font-medium px-3 py-2">Invoice</th>
                                                        <th class="text-left font-medium px-3 py-2">Customer</th>
                                                        <th class="text-left font-medium px-3 py-2">Date</th>
                                                        <th class="text-right font-medium px-3 py-2">Amount</th>
                                                        <th class="text-right font-medium px-3 py-2">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="invoice in mockInvoices" :key="invoice.number" class="border-b border-border last:border-b-0">
                                                        <td class="px-3 py-2 text-primary font-medium">{{ invoice.number }}</td>
                                                        <td class="px-3 py-2 text-text">{{ invoice.customer }}</td>
                                                        <td class="px-3 py-2 text-text-secondary">{{ invoice.date }}</td>
                                                        <td class="px-3 py-2 text-right text-text">{{ invoice.amount }}</td>
                                                        <td class="px-3 py-2 text-right">
                                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 bg-success/10 text-success text-[10px] font-semibold">
                                                                paid
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-3 grid grid-cols-3 gap-2">
                                            <button
                                                v-for="action in mockQuickActions"
                                                :key="action"
                                                type="button"
                                                class="rounded-lg border border-border bg-white py-2 text-[11px] font-semibold text-text hover:border-primary/30 transition-colors"
                                            >
                                                {{ action }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="py-20">
            <div class="max-w-6xl mx-auto px-6">
                <div class="max-w-2xl mb-12">
                    <UiAppBadge variant="info" class="mb-4">Workflow stack</UiAppBadge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text">
                        Quote, approval, invoice - one connected system.
                    </h2>
                    <p class="mt-4 text-text-secondary leading-relaxed">
                        Stop jumping between tools that break context. EasyRP keeps the commercial flow in one place.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <article
                        v-for="feature in features"
                        :key="feature.title"
                        class="rounded-2xl border border-border bg-white p-6 hover:shadow-card transition-all"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center" :class="feature.iconBg">
                                <component :is="feature.icon" class="w-5 h-5" :class="feature.iconColor" />
                            </div>
                            <span v-if="feature.plan" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary text-white">
                                {{ feature.plan }}
                            </span>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-text">{{ feature.title }}</h3>
                        <p class="mt-2 text-sm text-text-secondary leading-relaxed">{{ feature.desc }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="py-20 bg-white/45 border-y border-border/70">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <UiAppBadge variant="success" class="mb-4">How it feels</UiAppBadge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text">
                        Designed for speed from first message to payment.
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <article
                        v-for="(step, i) in steps"
                        :key="step.title"
                        class="rounded-2xl border border-border bg-white p-6"
                    >
                        <div class="w-11 h-11 rounded-xl bg-primary/8 border border-primary/15 flex items-center justify-center mb-4">
                            <component :is="step.icon" class="w-5 h-5 text-primary" />
                        </div>
                        <p class="text-xs font-semibold text-primary mb-2">STEP {{ i + 1 }}</p>
                        <h3 class="text-base font-semibold text-text mb-2">{{ step.title }}</h3>
                        <p class="text-sm text-text-secondary leading-relaxed">{{ step.desc }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="pricing" class="py-20">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <UiAppBadge variant="warning" class="mb-4">Pricing</UiAppBadge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text">
                        Plans that grow with your volume
                    </h2>
                    <p class="mt-4 text-text-secondary leading-relaxed">
                        Start free, upgrade when your pipeline grows.
                    </p>
                    <div class="flex items-center justify-center gap-3 mt-8">
                        <button class="flex items-center gap-3 group" @click="yearly = !yearly">
                            <span class="text-sm transition-colors duration-200" :class="!yearly ? 'text-text font-semibold' : 'text-text-muted'">Monthly</span>
                            <div class="relative w-12 h-6 rounded-full bg-primary shrink-0">
                                <span class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200" :class="yearly ? 'translate-x-6' : 'translate-x-0'" />
                            </div>
                            <span class="text-sm transition-colors duration-200" :class="yearly ? 'text-text font-semibold' : 'text-text-muted'">
                                Yearly
                                <span class="text-xs text-success font-semibold ml-1">Save up to {{ yearlyDiscountPercent }}%</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-5xl mx-auto">
                    <article
                        v-for="plan in plans"
                        :key="plan.name"
                        class="relative rounded-2xl p-6 flex flex-col border bg-white shadow-sm transition-all duration-300"
                        :class="plan.popular ? 'border-primary shadow-lg scale-[1.01]' : 'border-border hover:shadow-md'"
                    >
                        <div v-if="plan.popular" class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                            <span class="text-sm font-semibold px-3 py-1 rounded-full bg-primary text-white">Most popular</span>
                        </div>
                        <h3 class="text-xl font-bold text-text">{{ plan.name }}</h3>
                        <p class="text-base text-text-secondary mt-1 mb-4">{{ plan.desc }}</p>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span v-if="(yearly ? plan.yearlyPrice : plan.monthlyPrice) === '0'" class="text-4xl font-black text-text">Free</span>
                            <template v-else>
                                <span class="text-4xl font-black text-text">€{{ yearly ? plan.yearlyPrice : plan.monthlyPrice }}</span>
                                <span class="text-base text-text-muted">{{ yearly ? "/yr" : "/mo" }}</span>
                            </template>
                        </div>
                        <ul class="space-y-3 mb-8 flex-1">
                            <li v-for="feat in plan.features" :key="feat" class="flex items-start gap-2.5 text-base text-text-secondary leading-snug">
                                <svg class="w-4 h-4 text-success shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ feat }}
                            </li>
                        </ul>
                        <NuxtLink
                            to="/auth/register"
                            class="mt-auto inline-flex justify-center items-center text-sm font-semibold rounded-xl px-5 py-3 transition-all duration-200"
                            :class="plan.popular ? 'marketing-cta text-white hover:shadow-elevated' : 'bg-surface-alt text-text border border-border hover:border-primary/30 hover:shadow-card'"
                        >
                            {{ plan.cta }}
                        </NuxtLink>
                    </article>
                </div>
            </div>
        </section>

        <section id="faq" class="py-20">
            <div class="max-w-3xl mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text">
                        Frequently asked questions
                    </h2>
                    <p class="mt-4 text-text-secondary leading-relaxed">
                        Browse the
                        <NuxtLink to="/quote-to-invoice-software" class="text-primary hover:underline">quote-to-invoice guides</NuxtLink>
                        or contact support@easy-rp.com.
                    </p>
                </div>
                <div class="space-y-3">
                    <div v-for="(faq, i) in faqs" :key="i" class="rounded-xl border border-border bg-white overflow-hidden">
                        <button
                            class="w-full flex items-center justify-between px-6 py-4 text-left text-sm font-semibold text-text hover:bg-surface-alt transition-colors"
                            @click="openFaq = openFaq === i ? -1 : i"
                        >
                            {{ faq.q }}
                            <svg class="w-4 h-4 text-text-muted shrink-0 transition-transform duration-200" :class="openFaq === i ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
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
                                <p class="px-6 pb-4 text-sm text-text-secondary leading-relaxed">{{ faq.a }}</p>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 sm:py-24">
            <div class="max-w-3xl mx-auto px-6 text-center">
                <div class="marketing-dark-panel rounded-3xl px-8 py-14 sm:px-16 relative overflow-hidden">
                    <div class="absolute -top-20 -right-20 w-60 h-60 bg-primary/20 rounded-full blur-3xl" />
                    <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-secondary/20 rounded-full blur-3xl" />
                    <div class="relative z-10">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                            Ready to move from quote to paid invoice faster?
                        </h2>
                        <p class="mt-4 text-white/70 leading-relaxed max-w-md mx-auto">
                            Start free and run your entire quote-to-cash workflow in one place.
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center gap-3 mt-8">
                            <NuxtLink to="/auth/register" class="bg-white text-text font-semibold px-7 py-3 rounded-xl hover:shadow-elevated transition-all duration-200 text-sm">
                                Get started for free →
                            </NuxtLink>
                            <a href="mailto:support@easy-rp.com" class="border border-white/20 text-white font-medium px-7 py-3 rounded-xl hover:bg-white/10 transition-all duration-200 text-sm">
                                Contact support
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
    Package,
    Users,
    CreditCard,
    BarChart2,
    Zap,
    Mail,
    FileCheck,
} from "lucide-vue-next";

definePageMeta({
    layout: false,
});

const config = useRuntimeConfig();
const siteUrl = computed(() =>
    String(config.public.siteUrl || "").replace(/\/$/, ""),
);

const landingTitle =
    "Quote to invoice automation software for freelancers & small businesses";
const landingDescription =
    "EasyRP helps you send quotes online, capture acceptance by email, and generate invoices automatically — the freelance invoicing tool built around quote-to-invoice automation.";

const faqs = [
    {
        q: "How does quote to invoice automation work?",
        a: "You create structured offers (quotes) in EasyRP, email them to clients, and when they accept, line items and totals carry forward into invoices automatically — no second round of manual entry.",
    },
    {
        q: "Can customers accept quotes from email?",
        a: "Yes. Quotes are built to be shared digitally so recipients can approve clearly documented scopes and pricing; those acceptance signals tie back to your workspace records.",
    },
    {
        q: "What's included in the free trial?",
        a: "The 14-day free trial gives you all Starter features — offers, invoices, products, inventory, customers, orders, and payments — up to 25 records each. No credit card required. When the trial ends, choose a paid plan to keep your data and lift the limits.",
    },
    {
        q: "What's the difference between Starter and Pro?",
        a: "Starter ($19/mo) covers core operations with limits of 250 records per feature. Pro ($49/mo) removes all record limits and unlocks Reports & Analytics for revenue insights and sales breakdowns.",
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

useMarketingSeo({
    title: landingTitle,
    description: landingDescription,
    path: "/",
    keywords: [
        "quote to invoice automation",
        "online quote generator",
        "invoice automation software",
        "send quotes and convert to invoices",
        "freelance invoicing tool",
    ],
    faqs: faqs.map((f) => ({ question: f.q, answer: f.a })),
});

useHead(() => {
    const base = siteUrl.value;
    if (!base) {
        return {};
    }

    const jsonLd = {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                name: "EasyRP",
                url: base,
                logo: `${base}/favicon.svg`,
            },
            {
                "@type": "WebSite",
                name: "EasyRP",
                url: base,
                description: landingDescription,
            },
            {
                "@type": "SoftwareApplication",
                name: "EasyRP",
                applicationCategory: "BusinessApplication",
                operatingSystem: "Web",
                description: landingDescription,
                url: base,
                offers: {
                    "@type": "Offer",
                    price: "0",
                    priceCurrency: "USD",
                },
            },
        ],
    };

    return {
        script: [
            {
                key: "jsonld-easyrp-org",
                type: "application/ld+json",
                children: JSON.stringify(jsonLd),
            },
        ],
    };
});

const yearly = ref(false);
const openFaq = ref(-1);

const mockStats = [
    {
        label: "Revenue (30d)",
        value: "$24,500",
        change: "↑ 12% vs last month",
        up: true,
    },
    {
        label: "Quotes accepted",
        value: "12",
        change: "↑ 3 converted this week",
        up: true,
    },
    {
        label: "Invoices issued",
        value: "38",
        change: "↑ 4 new this week",
        up: true,
    },
    {
        label: "Outstanding",
        value: "$3.2k",
        change: "↓ vs last month",
        up: false,
    },
];

const dashboardSidebarItems = [
    "Dashboard",
    "Products",
    "Customers",
    "Offers",
    "Invoices",
    "Payments",
    "Branding",
    "Billing",
];

const mockInvoices = [
    { number: "INV-2026-054028", customer: "John Doe", date: "May 04, 2026", amount: "100,00 EUR" },
    { number: "INV-2026-591035", customer: "John Doe", date: "May 04, 2026", amount: "100,00 EUR" },
];

const mockQuickActions = ["New Product", "New Invoice", "New Customer"];

const features = [
    {
        icon: FileText,
        iconBg: "bg-primary/10",
        iconColor: "text-primary",
        title: "Offers & professional quotes",
        desc: "Build catalog-backed proposals — your online quote generator with pricing, taxes, and reusable descriptions.",
        plan: null,
    },
    {
        icon: Mail,
        iconBg: "bg-secondary/10",
        iconColor: "text-secondary",
        title: "Send quotes online",
        desc: "Email polished quotes so clients see clear totals and scopes — acceptance stays tied to your workspace.",
        plan: null,
    },
    {
        icon: FileCheck,
        iconBg: "bg-success/10",
        iconColor: "text-success",
        title: "Convert quotes to invoices",
        desc: "Invoice automation software behavior: approvals carry line items forward so invoices mirror what customers authorized.",
        plan: null,
    },
    {
        icon: Users,
        iconBg: "bg-warning/10",
        iconColor: "text-warning",
        title: "Customer records",
        desc: "Keep contacts, histories, and billing details aligned from first quote through final payment.",
        plan: null,
    },
    {
        icon: CreditCard,
        iconBg: "bg-info/10",
        iconColor: "text-info",
        title: "Payments & orders",
        desc: "Accept payments, track balances, and manage orders alongside the documents customers already recognize.",
        plan: null,
    },
    {
        icon: Package,
        iconBg: "bg-primary/10",
        iconColor: "text-primary",
        title: "Inventory awareness",
        desc: "Optional stock visibility so quoted quantities align with what you can fulfill.",
        plan: null,
    },
    {
        icon: BarChart2,
        iconBg: "bg-danger/10",
        iconColor: "text-danger",
        title: "Reports & analytics",
        desc: "Revenue and pipeline clarity once quotes convert — dashboards on Pro for deeper breakdowns.",
        plan: "Pro",
    },
];

const steps = [
    {
        icon: Zap,
        title: "Create & send quotes",
        desc: "Draft structured offers from your catalog and deliver them as professional online quotes ready for clients.",
    },
    {
        icon: Mail,
        title: "Clients accept from email",
        desc: "Recipients review totals and approve digitally — no copy-pasting scopes into invoices later.",
    },
    {
        icon: FileCheck,
        title: "Invoices generate automatically",
        desc: "Accepted quotes feed invoice automation so billing matches approvals and speeds up payment cycles.",
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
        desc: "For freelancers & growing SMBs",
        monthlyPrice: "19",
        yearlyPrice: "150",
        popular: false,
        cta: "Get started",
        features: [
            "Up to 250 offers & invoices",
            "Up to 250 products & customers",
            "Up to 250 orders",
            "Payments & inventory (unlimited)",
            "Email support (support@easy-rp.com)",
        ],
    },
    {
        name: "Pro",
        desc: "For growing businesses",
        monthlyPrice: "49",
        yearlyPrice: "450",
        popular: true,
        cta: "Start free trial",
        features: [
            "Everything in Starter — unlimited",
            "Reports & analytics",
            "Priority support via support@easy-rp.com",
        ],
    },
];

const yearlyDiscountPercent = (() => {
    const paidPlans = plans.filter(
        (plan) => Number(plan.monthlyPrice) > 0 && Number(plan.yearlyPrice) > 0,
    );

    const discounts = paidPlans
        .map((plan) => {
            const monthly = Number(plan.monthlyPrice);
            const yearly = Number(plan.yearlyPrice);
            const yearlyAtMonthlyRate = monthly * 12;
            return ((yearlyAtMonthlyRate - yearly) / yearlyAtMonthlyRate) * 100;
        })
        .filter((discount) => Number.isFinite(discount) && discount > 0);

    if (discounts.length === 0) {
        return 0;
    }

    return Math.round(Math.max(...discounts));
})();

</script>
