<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class OfferPdfService
{
    /**
     * Generate a PDF for the given offer, persist it to storage, and update
     * `offer->pdf_path`. Returns the updated Offer model.
     */
    public function generate(Offer $offer): Offer
    {
        // Ensure relationships needed by the template are loaded.
        $offer->loadMissing(['customer', 'items.product']);

        $tenantId = auth()->user()?->tenant_id ?? $this->resolveTenantId();
        $tenant = auth()->user()?->tenant ?? ($tenantId ? Tenant::find($tenantId) : null);
        $tenant ??= (object) ['name' => config('app.name')];
        $tenantPath = $tenantId ? (string) $tenantId : 'shared';

        $logoPath = null;
        if ($tenant instanceof Tenant && $tenant->logo_path && Storage::disk('public')->exists($tenant->logo_path)) {
            $logoPath = Storage::disk('public')->path($tenant->logo_path);
        }

        $pdf = Pdf::loadView('pdf.offer', [
            'offer'  => $offer,
            'tenant' => $tenant,
            'logoPath' => $logoPath,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $relativePath = "offers/{$tenantPath}/{$offer->offer_number}.pdf";

        // Ensure the directory exists.
        Storage::disk('public')->makeDirectory("offers/{$tenantPath}");

        Storage::disk('public')->put($relativePath, $pdf->output());

        // Store the path so Storage::disk('public')->url() works.
        $offer->pdf_path = $relativePath;
        $offer->save();

        return $offer;
    }

    /**
     * Delete the PDF file for the given offer from storage.
     */
    public function delete(Offer $offer): void
    {
        if ($offer->pdf_path && Storage::disk('public')->exists($offer->pdf_path)) {
            Storage::disk('public')->delete($offer->pdf_path);
        }
    }

    private function resolveTenantId(): ?int
    {
        return TenantDatabaseService::tenantIdFromSearchPath(
            config('database.connections.tenant.search_path')
        );
    }
}
