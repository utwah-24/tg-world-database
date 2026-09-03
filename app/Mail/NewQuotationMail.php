<?php

namespace App\Mail;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewQuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quotation $quotation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New quotation {$this->quotation->reference} — ".($this->quotation->vehicle_snapshot['title'] ?? 'Vehicle request'),
            replyTo: [new Address($this->quotation->email, $this->quotation->full_name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-quotation',
            with: [
                'logoUrl' => rtrim((string) config('app.media_url'), '/').'/logo-dark.jpeg',
                'dashboardUrl' => rtrim((string) config('app.url'), '/').'/admin/quotations',
            ],
        );
    }
}
