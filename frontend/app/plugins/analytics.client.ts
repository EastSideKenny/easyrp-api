/**
 * Loads GA4 when `runtimeConfig.public.googleAnalyticsMeasurementId` is set.
 * Scripts are appended after mount so they do not block first paint.
 */
export default defineNuxtPlugin((nuxtApp) => {
    const id = useRuntimeConfig().public.googleAnalyticsMeasurementId;
    if (!id) {
        return;
    }

    nuxtApp.hook("app:mounted", () => {
        const load = document.createElement("script");
        load.async = true;
        load.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`;
        document.head.appendChild(load);

        const inline = document.createElement("script");
        inline.textContent = `
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', ${JSON.stringify(id)}, { send_page_view: true, anonymize_ip: true });
`;
        document.head.appendChild(inline);
    });
});
