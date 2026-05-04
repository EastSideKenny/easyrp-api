<template>
    <NuxtLayout name="landing">
        <!-- ─── Hero ─── -->
        <section
            class="relative overflow-hidden bg-grid pt-28 pb-20 sm:pt-36 sm:pb-28"
        >
            <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
                <UiAppBadge variant="primary" class="mb-6 animate-fade-in-up">
                    Quote to invoice automation — free to start
                </UiAppBadge>

                <h1
                    class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-text leading-[1.1] max-w-3xl mx-auto animate-fade-in-up stagger-1"
                >
                    From approved quotes to paid invoices —
                    <span
                        class="bg-linear-to-r from-primary to-secondary bg-clip-text text-transparent"
                        >without busywork.</span
                    >
                </h1>

                <p
                    class="mt-6 text-lg sm:text-xl text-text-secondary max-w-xl mx-auto leading-relaxed animate-fade-in-up stagger-2"
                >
                    Quote-to-invoice automation for freelancers and small
                    businesses: send quotes customers accept from email, turn
                    approvals into invoices, and add inventory, payments, and
                    reporting when you're ready.
                </p>

                <nav
                    class="mt-7 flex flex-wrap justify-center gap-x-4 gap-y-2 text-xs text-text-muted max-w-2xl mx-auto animate-fade-in-up stagger-2"
                    aria-label="In-depth guides"
                >
                    <NuxtLink
                        to="/quote-to-invoice-software"
                        class="hover:text-primary underline-offset-2 hover:underline"
                        >Quote to invoice software</NuxtLink
                    >
                    <span class="text-border hidden sm:inline" aria-hidden="true"
                        >·</span
                    >
                    <NuxtLink
                        to="/invoice-automation"
                        class="hover:text-primary underline-offset-2 hover:underline"
                        >Invoice automation</NuxtLink
                    >
                    <span class="text-border hidden sm:inline" aria-hidden="true"
                        >·</span
                    >
                    <NuxtLink
                        to="/freelance-invoicing-tool"
                        class="hover:text-primary underline-offset-2 hover:underline"
                        >Freelance invoicing tool</NuxtLink
                    >
                    <span class="text-border hidden sm:inline" aria-hidden="true"
                        >·</span
                    >
                    <NuxtLink
                        to="/send-quotes-online"
                        class="hover:text-primary underline-offset-2 hover:underline"
                        >Send quotes online</NuxtLink
                    >
                </nav>

                <div
                    class="flex flex-col sm:flex-row justify-center gap-3 mt-8 animate-fade-in-up stagger-3"
                >
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary/20 text-base px-6 py-3 bg-primary text-white hover:bg-primary-dark hover:shadow-elevated"
                        @click="navigateTo('/auth/register')"
                    >
                        Start for free →
                    </button>
                </div>

                <p
                    class="mt-4 text-xs text-text-muted animate-fade-in-up stagger-4"
                >
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
                                    <div
                                        class="w-3 h-3 rounded-full bg-danger/60"
                                    />
                                    <div
                                        class="w-3 h-3 rounded-full bg-warning/60"
                                    />
                                    <div
                                        class="w-3 h-3 rounded-full bg-success/60"
                                    />
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
                                    <p class="text-xs text-text-muted">
                                        {{ stat.label }}
                                    </p>
                                    <p class="text-lg font-bold text-text mt-1">
                                        {{ stat.value }}
                                    </p>
                                    <p
                                        class="text-xs mt-1"
                                        :class="
                                            stat.up
                                                ? 'text-success'
                                                : 'text-danger'
                                        "
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
                                    <p
                                        class="text-xs font-medium text-text-muted"
                                    >
                                        Quote → invoice activity
                                    </p>
                                    <div
                                        v-for="i in 3"
                                        :key="i"
                                        class="flex items-center gap-2"
                                    >
                                        <div
                                            class="w-6 h-6 rounded-full bg-primary/10 shrink-0"
                                        />
                                        <div class="flex-1">
                                            <div
                                                class="h-2 bg-border rounded-full w-full"
                                            />
                                            <div
                                                class="h-2 bg-border rounded-full w-2/3 mt-1"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </UiAppCard>
                </div>
            </div>
        </section>

        <!-- ─── Features ─── -->
        <section id="features" class="py-20 sm:py-28">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <UiAppBadge variant="info" class="mb-4">
                        Offers → invoices → payments
                    </UiAppBadge>
                    <h2
                        class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text"
                    >
                        Quote to invoice automation in one workspace
                    </h2>
                    <p class="mt-4 text-text-secondary leading-relaxed">
                        Stop rebuilding line items twice. EasyRP connects your
                        online quote generator moments with invoice automation
                        software workflows — plus inventory, customers, and
                        payments when operations scale beyond solo billing.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5"
                >
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
                    <UiAppBadge variant="success" class="mb-4">
                        Quote → invoice flow
                    </UiAppBadge>
                    <h2
                        class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text"
                    >
                        From first quote to collected payment
                    </h2>
                    <p class="mt-4 text-text-secondary leading-relaxed">
                        EasyRP is built around how freelancers and small teams
                        actually sell: send quotes online, capture acceptance by
                        email, then let invoices inherit approved scopes — no
                        duplicate line-item wrangling.
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
                            <component
                                :is="step.icon"
                                class="w-7 h-7 text-primary"
                            />
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
                        Plans for quote-to-invoice workflows
                    </h2>
                    <p class="mt-4 text-text-secondary leading-relaxed">
                        Try offers, invoicing, inventory, and payments free for 14
                        days. Upgrade when you're ready — no hidden fees, no
                        per-seat charges.
                    </p>

                    <!-- Billing toggle -->
                    <div class="flex items-center justify-center gap-3 mt-8">
                        <button
                            class="flex items-center gap-3 group"
                            @click="yearly = !yearly"
                        >
                            <span
                                class="text-sm transition-colors duration-200"
                                :class="
                                    !yearly
                                        ? 'text-text font-semibold'
                                        : 'text-text-muted'
                                "
                                >Monthly</span
                            >
                            <div
                                class="relative w-12 h-6 rounded-full bg-primary flex-shrink-0"
                            >
                                <span
                                    class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                                    :class="
                                        yearly
                                            ? 'translate-x-6'
                                            : 'translate-x-0'
                                    "
                                />
                            </div>
                            <span
                                class="text-sm transition-colors duration-200"
                                :class="
                                    yearly
                                        ? 'text-text font-semibold'
                                        : 'text-text-muted'
                                "
                            >
                                Yearly
                                <span
                                    class="text-xs text-success font-semibold ml-1"
                                    >Save up to {{ yearlyDiscountPercent }}%</span
                                >
                            </span>
                        </button>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-4xl mx-auto"
                >
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
                        <h3 class="text-base font-semibold text-text">
                            {{ plan.name }}
                        </h3>
                        <p class="text-sm text-text-secondary mt-1 mb-4">
                            {{ plan.desc }}
                        </p>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span
                                v-if="
                                    (yearly
                                        ? plan.yearlyPrice
                                        : plan.monthlyPrice) === '0'
                                "
                                class="text-3xl font-extrabold text-text"
                                >Free</span
                            >
                            <template v-else>
                                <span class="text-3xl font-extrabold text-text">
                                    €{{
                                        yearly
                                            ? plan.yearlyPrice
                                            : plan.monthlyPrice
                                    }}
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
                        <NuxtLink
                            to="/auth/register"
                            class="mt-auto inline-flex justify-center items-center text-sm font-semibold rounded-xl px-5 py-2.5 transition-all duration-200"
                            :class="
                                plan.popular
                                    ? 'bg-primary text-white hover:bg-primary-dark hover:shadow-elevated'
                                    : 'bg-surface-alt text-text border border-border hover:border-primary/30 hover:shadow-card'
                            "
                        >
                            {{ plan.cta }}
                        </NuxtLink>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── FAQ ─── -->
        <section id="faq" class="py-20 sm:py-28">
            <div class="max-w-3xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2
                        class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text"
                    >
                        Frequently asked questions
                    </h2>
                    <p class="mt-4 text-text-secondary leading-relaxed">
                        Browse the
                        <NuxtLink
                            to="/quote-to-invoice-software"
                            class="text-primary hover:underline"
                            >quote-to-invoice guides</NuxtLink
                        >
                        or
                        <a href="#" class="text-primary hover:underline"
                            >reach out to our team</a
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
                            Ready to send quotes and convert to invoices faster?
                        </h2>
                        <p
                            class="mt-4 text-white/60 leading-relaxed max-w-md mx-auto"
                        >
                            Join freelancers and lean teams automating proposals,
                            approvals, and invoices with EasyRP — start free,
                            no credit card required.
                        </p>
                        <div
                            class="flex flex-col sm:flex-row justify-center gap-3 mt-8"
                        >
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

const chartBars = [30, 45, 35, 55, 40, 65, 50, 70, 55, 80, 60, 75];

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
            "Email support",
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
            "Priority support",
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
