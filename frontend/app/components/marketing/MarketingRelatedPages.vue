<template>
    <aside
        class="max-w-3xl mx-auto px-6 py-12 border-y border-border bg-surface"
        aria-labelledby="related-pages-heading"
    >
        <h2
            id="related-pages-heading"
            class="text-sm font-semibold text-text mb-4"
        >
            Related guides
        </h2>
        <ul class="grid sm:grid-cols-2 gap-3">
            <li v-for="page in links" :key="page.path">
                <NuxtLink
                    :to="page.path"
                    class="group block rounded-xl border border-border bg-surface-alt px-4 py-3 hover:border-primary/30 hover:shadow-card transition-all"
                >
                    <span
                        class="text-sm font-medium text-text group-hover:text-primary"
                        >{{ page.title }}</span
                    >
                    <span class="block text-xs text-text-muted mt-1 leading-snug">{{
                        page.description
                    }}</span>
                </NuxtLink>
            </li>
        </ul>
    </aside>
</template>

<script setup lang="ts">
import { MARKETING_SEO_RELATED_PAGES } from "~/constants/marketing-paths";

const props = defineProps<{
    currentPath: string;
}>();

const links = computed(() =>
    MARKETING_SEO_RELATED_PAGES.filter((p) => p.path !== props.currentPath),
);
</script>
