<template>
    <article class="marketing-shell border-b border-border/70">
        <header
            class="relative overflow-hidden bg-grid pt-28 pb-14 sm:pt-32 sm:pb-20 border-b border-border/70 marketing-layer"
        >
            <div class="max-w-3xl mx-auto px-6 text-center">
                <p
                    v-if="eyebrow"
                    class="text-xs font-semibold uppercase tracking-widest text-primary mb-4"
                >
                    {{ eyebrow }}
                </p>
                <slot name="hero" />
            </div>
        </header>

        <div
            class="max-w-3xl mx-auto px-6 py-14 sm:py-16 space-y-14 text-text-secondary leading-relaxed marketing-layer"
        >
            <slot />
        </div>

        <MarketingRelatedPages :current-path="currentPath" />

        <section
            class="max-w-3xl mx-auto px-6 pb-20"
            aria-labelledby="article-faq-heading"
        >
            <slot name="faq" />
        </section>

        <section class="max-w-3xl mx-auto px-6 pb-24">
            <div
                class="rounded-2xl marketing-glass p-8 sm:p-10 text-center"
            >
                <h2 class="text-xl font-bold text-text tracking-tight">
                    Ready to automate quotes and invoices?
                </h2>
                <p class="mt-3 text-sm text-text-secondary max-w-lg mx-auto">
                    Start free — send quotes online, let clients approve by email,
                    and generate invoices automatically with EasyRP.
                </p>
                <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary/20 text-base px-6 py-3 marketing-cta hover:shadow-elevated"
                        @click="navigateTo('/auth/register')"
                    >
                        Start for free →
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary/10 text-base px-6 py-3 marketing-card-neo text-text hover:border-text-muted hover:shadow-card"
                        @click="navigateTo('/auth/login')"
                    >
                        Sign in
                    </button>
                </div>
            </div>
        </section>
    </article>
</template>

<script setup lang="ts">
defineProps<{
    eyebrow?: string;
    currentPath: string;
}>();
</script>
