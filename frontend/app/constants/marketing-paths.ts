/** Paths served as indexable marketing pages (SSR/prerender-friendly). */
export const MARKETING_INDEXABLE_PATHS = [
    "/",
    "/quote-to-invoice-software",
    "/invoice-automation",
    "/freelance-invoicing-tool",
    "/send-quotes-online",
] as const;

export type MarketingIndexablePath =
    (typeof MARKETING_INDEXABLE_PATHS)[number];

/** Internal SEO pillar pages for navigation and cross-linking. */
export const MARKETING_SEO_RELATED_PAGES: {
    path: string;
    title: string;
    description: string;
}[] = [
    {
        path: "/",
        title: "Home",
        description: "Quote to invoice automation overview",
    },
    {
        path: "/quote-to-invoice-software",
        title: "Quote to invoice software",
        description:
            "Automate quotes that convert into invoices without manual data entry.",
    },
    {
        path: "/invoice-automation",
        title: "Invoice automation",
        description:
            "Generate invoices automatically when clients approve your quotes.",
    },
    {
        path: "/freelance-invoicing-tool",
        title: "Freelance invoicing tool",
        description:
            "Send quotes and invoices built for freelancers and lean teams.",
    },
    {
        path: "/send-quotes-online",
        title: "Send quotes online",
        description:
            "Professional online quotes customers can accept from email.",
    },
];
