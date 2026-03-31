<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    /**
     * Generate a PDF for the given invoice, persist it to storage, and update
     * `invoice->pdf_path`. Returns the updated Invoice model.
     */
    public function generate(Invoice $invoice): Invoice
    {
        // Ensure relationships needed by the template are loaded.
        $invoice->loadMissing(['tenant', 'customer', 'items.product']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice'  => $invoice,
            'tenant' => $invoice->tenant,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $relativePath = "invoices/{$invoice->tenant_id}/{$invoice->invoice_number}.pdf";

        // Ensure the directory exists.
        Storage::disk('public')->makeDirectory("invoices/{$invoice->tenant_id}");

        Storage::disk('public')->put($relativePath, $pdf->output());

        // Store the path so Storage::disk('public')->url() works.
        $invoice->pdf_path = $relativePath;
        $invoice->save();

        return $invoice;
    }

    /**
     * Delete the PDF file for the given invoice from storage.
     */
    public function delete(Invoice $invoice): void
    {
        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            Storage::disk('public')->delete($invoice->pdf_path);
        }
    }
}
