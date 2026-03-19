<?php

namespace App\Services;

use App\Models\Offer;
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
        $offer->loadMissing(['tenant', 'customer', 'items.product']);

        $pdf = Pdf::loadView('pdf.offer', [
            'offer'  => $offer,
            'tenant' => $offer->tenant,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $relativePath = "offers/{$offer->tenant_id}/{$offer->offer_number}.pdf";

        // Ensure the directory exists.
        Storage::disk('public')->makeDirectory("offers/{$offer->tenant_id}");

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
}
