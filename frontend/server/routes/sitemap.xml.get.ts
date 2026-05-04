/** Keep in sync with `app/constants/marketing-paths.ts` MARKETING_INDEXABLE_PATHS */
const MARKETING_INDEXABLE_PATHS = [
    "/",
    "/quote-to-invoice-software",
    "/invoice-automation",
    "/freelance-invoicing-tool",
    "/send-quotes-online",
] as const;

export default defineEventHandler((event) => {
    const base = String(useRuntimeConfig(event).public.siteUrl || "").replace(
        /\/$/,
        "",
    );

    setResponseHeader(event, "Content-Type", "application/xml; charset=utf-8");
    setResponseHeader(event, "Cache-Control", "public, max-age=3600");

    if (!base) {
        return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>`;
    }

    const urls = [...MARKETING_INDEXABLE_PATHS].map((p) =>
        p === "/" ? `${base}/` : `${base}${p}`,
    );

    const body = urls
        .map((loc, i) => {
            const priority = i === 0 ? "1.0" : "0.85";
            return `
  <url>
    <loc>${escapeXml(loc)}</loc>
    <changefreq>weekly</changefreq>
    <priority>${priority}</priority>
  </url>`;
        })
        .join("");

    return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${body}
</urlset>`;
});

function escapeXml(s: string): string {
    return s
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}
