<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Mail\Concerns\SetsTenantSchema;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SetsTenantSchema, SerializesModels {
        SetsTenantSchema::__unserialize insteadof SerializesModels;
        SerializesModels::__unserialize as unserializeFromSerializesModels;
    }

    public function __construct(public Order $order)
    {
        $this->initializeSetsTenantSchema();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order {$this->order->order_number} Confirmation",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.order-confirmation');
    }
}
