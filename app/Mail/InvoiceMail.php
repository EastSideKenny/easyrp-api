<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\SetsTenantSchema;
use App\Services\InvoicePdfService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SetsTenantSchema, SerializesModels {
        SetsTenantSchema::__unserialize insteadof SerializesModels;
        SerializesModels::__unserialize as unserializeFromSerializesModels;
    }

    public function __construct(public Invoice $invoice)
    {
        $this->initializeSetsTenantSchema();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_number} from {$this->tenantName}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.invoice');
    }

    public function attachments(): array
    {
        // Ensure PDF exists
        if (!$this->invoice->pdf_path || !Storage::disk('public')->exists($this->invoice->pdf_path)) {
            Log::info('[InvoiceMail] Generating PDF for invoice', ['invoice_id' => $this->invoice->id]);
            app(InvoicePdfService::class)->generate($this->invoice);
        }
        $pdfPath = Storage::disk('public')->path($this->invoice->pdf_path);
        $exists = file_exists($pdfPath);
        Log::info('[InvoiceMail] Attaching PDF', ['pdf_path' => $pdfPath, 'exists' => $exists]);
        return [
            \Illuminate\Mail\Attachment::fromPath($pdfPath)
                ->as('Invoice-' . $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
