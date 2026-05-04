export default defineEventHandler((event) => {
    const base = String(useRuntimeConfig(event).public.siteUrl || "").replace(
        /\/$/,
        "",
    );

    const lines = ["User-agent: *", "Allow: /"];

    if (base) {
        lines.push("", `Sitemap: ${base}/sitemap.xml`);
    }

    setResponseHeader(event, "Content-Type", "text/plain; charset=utf-8");
    setResponseHeader(event, "Cache-Control", "public, max-age=3600");

    return lines.join("\n");
});
