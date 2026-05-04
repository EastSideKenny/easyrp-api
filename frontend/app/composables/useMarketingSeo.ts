/**
 * Canonical marketing SEO helper layered on Nuxt’s `useSeoMeta` / `useHead`:
 * titles, descriptions, Open Graph, Twitter cards, canonical URLs, keywords,
 * FAQ JSON-LD, and optional extra schema.org graph nodes.
 */
export interface MarketingFaqItem {
    question: string;
    answer: string;
}

export interface UseMarketingSeoOptions {
    /** Primary meta title (brand suffix applied globally via titleTemplate where configured). */
    title: string;
    /** Meta description (~150–160 chars recommended). */
    description: string;
    /** Canonical path including leading slash; defaults to current route.path */
    path?: string;
    keywords?: string[];
    /** Absolute or relative OG image path; defaults to /og-image.png on origin */
    ogImage?: string;
    ogImageAlt?: string;
    /** FAQ entries merged into FAQPage JSON-LD for rich results */
    faqs?: MarketingFaqItem[];
    /** Additional schema.org nodes merged into @graph (e.g. Organization on homepage). */
    jsonLdGraphExtra?: Record<string, unknown>[];
}

function buildAbsoluteOgImage(siteUrl: string, ogImage?: string): string | undefined {
    if (!siteUrl) {
        return undefined;
    }
    if (!ogImage || ogImage.startsWith("http")) {
        return ogImage ?? `${siteUrl}/og-image.png`;
    }
    return `${siteUrl}${ogImage.startsWith("/") ? "" : "/"}${ogImage}`;
}

/**
 * Production-ready SEO defaults for marketing pages: meta, Open Graph, Twitter,
 * canonical URL, and optional FAQ / custom JSON-LD graph entries.
 */
export function useMarketingSeo(opts: UseMarketingSeoOptions) {
    const route = useRoute();
    const config = useRuntimeConfig();

    const siteUrl = String(config.public.siteUrl || "").replace(/\/$/, "");
    const path = opts.path ?? route.path;
    const canonicalHref = siteUrl ? `${siteUrl}${path === "/" ? "/" : path}` : undefined;

    const absoluteOg = buildAbsoluteOgImage(siteUrl, opts.ogImage);

    useHead({
        meta: opts.keywords?.length
            ? [{ name: "keywords", content: opts.keywords.join(", ") }]
            : [],
        link: canonicalHref ? [{ rel: "canonical", href: canonicalHref }] : [],
    });

    useSeoMeta({
        title: opts.title,
        description: opts.description,
        ogTitle: opts.title,
        ogDescription: opts.description,
        ogUrl: canonicalHref,
        ogType: "website",
        ogLocale: "en_US",
        ogSiteName: "EasyRP",
        twitterTitle: opts.title,
        twitterDescription: opts.description,
        ...(absoluteOg
            ? {
                  ogImage: absoluteOg,
                  ogImageWidth: 1200,
                  ogImageHeight: 630,
                  ogImageAlt:
                      opts.ogImageAlt ??
                      `${opts.title} — EasyRP quote to invoice automation`,
                  twitterCard: "summary_large_image" as const,
                  twitterImage: absoluteOg,
              }
            : {}),
    });

    const graph: Record<string, unknown>[] = [...(opts.jsonLdGraphExtra ?? [])];

    if (opts.faqs?.length) {
        graph.push({
            "@type": "FAQPage",
            mainEntity: opts.faqs.map((f) => ({
                "@type": "Question",
                name: f.question,
                acceptedAnswer: {
                    "@type": "Answer",
                    text: f.answer,
                },
            })),
        });
    }

    if (graph.length > 0 && siteUrl) {
        useHead({
            script: [
                {
                    key: `jsonld-graph-${path}`,
                    type: "application/ld+json",
                    children: JSON.stringify({
                        "@context": "https://schema.org",
                        "@graph": graph,
                    }),
                },
            ],
        });
    }
}
