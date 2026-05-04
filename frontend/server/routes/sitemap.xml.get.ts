import type { H3Event } from "h3";
import { getRequestHeaders, getRequestURL } from "h3";

/** Keep in sync with `app/constants/marketing-paths.ts` MARKETING_INDEXABLE_PATHS */
const MARKETING_INDEXABLE_PATHS = [
    "/",
    "/quote-to-invoice-software",
    "/invoice-automation",
    "/freelance-invoicing-tool",
    "/send-quotes-online",
] as const;

/**
 * Absolute site origin for <loc> values. Prefer NUXT_PUBLIC_SITE_URL in production;
 * fall back to the incoming request so the sitemap is never an empty <urlset>
 * (Search Console rejects that as “missing url tag”).
 */
function resolvePublicSiteUrl(event: H3Event): string {
    const configured = String(
        useRuntimeConfig(event).public.siteUrl || "",
    ).replace(/\/$/, "");
    if (configured) {
        return configured;
    }

    const headers = getRequestHeaders(event);
    const host = (headers["x-forwarded-host"] || headers.host || "")
        .split(",")[0]
        ?.trim();
    const proto = (headers["x-forwarded-proto"] || "https")
        .split(",")[0]
        ?.trim();
    if (host) {
        return `${proto || "https"}://${host}`.replace(/\/$/, "");
    }

    return getRequestURL(event).origin.replace(/\/$/, "");
}

export default defineEventHandler((event: H3Event) => {
    const base = resolvePublicSiteUrl(event);

    setResponseHeader(event, "Content-Type", "application/xml; charset=utf-8");
    setResponseHeader(event, "Cache-Control", "public, max-age=3600");

    const urls = [...MARKETING_INDEXABLE_PATHS].map((p) =>
        p === "/" ? `${base}/` : `${base}${p}`,
    );

    const urlElements = urls
        .map((loc, i) => {
            const priority = i === 0 ? "1.0" : "0.85";
            return [
                "  <url>",
                `    <loc>${escapeXml(loc)}</loc>`,
                "    <changefreq>weekly</changefreq>",
                `    <priority>${priority}</priority>`,
                "  </url>",
            ].join("\n");
        })
        .join("\n");

    return [
        '<?xml version="1.0" encoding="UTF-8"?>',
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        urlElements,
        "</urlset>",
        "",
    ].join("\n");
});

function escapeXml(s: string): string {
    return s
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}
