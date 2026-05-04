/** Injects Search Console verification meta when env is configured. */
export default defineNuxtPlugin(() => {
    const token = useRuntimeConfig().public.googleSearchConsoleVerification;
    if (!token) {
        return;
    }

    useHead({
        meta: [
            {
                name: "google-site-verification",
                content: token,
            },
        ],
    });
});
