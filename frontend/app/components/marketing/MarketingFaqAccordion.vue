<template>
    <div>
        <h2
            :id="headingId"
            class="text-2xl sm:text-3xl font-extrabold tracking-tight text-text text-center mb-10"
        >
            {{ heading }}
        </h2>
        <div class="space-y-3">
            <div
                v-for="(faq, i) in faqs"
                :key="i"
                class="bg-surface border border-border rounded-xl overflow-hidden"
            >
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-6 py-4 text-left text-sm font-medium text-text hover:bg-surface-alt transition-colors gap-4"
                    :aria-expanded="open === i"
                    :aria-controls="`faq-panel-${headingId}-${i}`"
                    @click="open = open === i ? -1 : i"
                >
                    {{ faq.q }}
                    <svg
                        class="w-4 h-4 text-text-muted shrink-0 transition-transform duration-200"
                        :class="open === i ? 'rotate-180' : ''"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </button>
                <div
                    :id="`faq-panel-${headingId}-${i}`"
                    role="region"
                    :hidden="open !== i"
                    class="border-t border-border"
                >
                    <p
                        v-show="open === i"
                        class="px-6 py-4 text-sm text-text-secondary leading-relaxed"
                    >
                        {{ faq.a }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        heading?: string;
        headingId?: string;
        faqs: { q: string; a: string }[];
    }>(),
    {
        heading: "Frequently asked questions",
        headingId: "faq-heading",
    },
);

const open = ref(-1);
</script>
