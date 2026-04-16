<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\SetsTenantSchema;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable implements ShouldQueue
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
            subject: "Payment Received for Invoice {$this->invoice->invoice_number}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.payment-received');
    }
}
