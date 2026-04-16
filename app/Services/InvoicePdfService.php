<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
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
        $invoice->loadMissing(['customer', 'items.product']);

        $tenantId = auth()->user()?->tenant_id ?? $this->resolveTenantId();
        $tenant = auth()->user()?->tenant ?? ($tenantId ? Tenant::find($tenantId) : null);
        $tenant ??= (object) ['name' => config('app.name')];
        $tenantPath = $tenantId ? (string) $tenantId : 'shared';

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice'  => $invoice,
            'tenant' => $tenant,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $relativePath = "invoices/{$tenantPath}/{$invoice->invoice_number}.pdf";

        // Ensure the directory exists.
        Storage::disk('public')->makeDirectory("invoices/{$tenantPath}");

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

    private function resolveTenantId(): ?int
    {
        $searchPath = (string) config('database.connections.tenant.search_path', '');

        foreach (explode(',', $searchPath) as $schema) {
            if (preg_match('/^tenant_(\d+)$/', trim($schema), $matches)) {
                return (int) $matches[1];
            }
        }

        return null;
    }
}
