<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\SetsTenantSchema;
use App\Services\OfferPdfService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;

class OfferMail extends Mailable implements ShouldQueue
{
    use Queueable, SetsTenantSchema, SerializesModels {
        SetsTenantSchema::__unserialize insteadof SerializesModels;
        SerializesModels::__unserialize as unserializeFromSerializesModels;
    }

    public string $acceptUrl;
    public string $declineUrl;

    public function __construct(public Offer $offer)
    {
        $this->initializeSetsTenantSchema();
        $frontendUrl = config('app.frontend_url');
        $this->acceptUrl = "{$frontendUrl}/offers/{$offer->id}/respond?action=accept&token={$offer->token}";
        $this->declineUrl = "{$frontendUrl}/offers/{$offer->id}/respond?action=decline&token={$offer->token}";
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Offer {$this->offer->offer_number} from {$this->tenantName}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.offer');
    }

    public function attachments(): array
    {
        // Ensure PDF exists
        if (!$this->offer->pdf_path || !Storage::disk('public')->exists($this->offer->pdf_path)) {
            Log::info('[OfferMail] Generating PDF for offer', ['offer_id' => $this->offer->id]);
            app(OfferPdfService::class)->generate($this->offer);
        }
        $pdfPath = Storage::disk('public')->path($this->offer->pdf_path);
        $exists = file_exists($pdfPath);
        Log::info('[OfferMail] Attaching PDF', ['pdf_path' => $pdfPath, 'exists' => $exists]);
        return [
            \Illuminate\Mail\Attachment::fromPath($pdfPath)
                ->as('Offer-' . $this->offer->offer_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
