import { MARKETING_INDEXABLE_PATHS } from "~/constants/marketing-paths";

const MARKETING_INDEX_SET = new Set<string>(MARKETING_INDEXABLE_PATHS);

/**
 * Robots directives: marketing URLs stay indexable; app and auth stay private.
 * Page-level OG/canonical/meta live in useMarketingSeo() on indexable routes.
 */
export default defineNuxtPlugin(() => {
    const route = useRoute();

    const indexable = MARKETING_INDEX_SET.has(route.path);

    const robotsContent = indexable ? "index, follow" : "noindex, nofollow";

    useHead({
        meta: [{ name: "robots", content: robotsContent }],
    });
});
