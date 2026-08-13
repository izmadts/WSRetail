<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailables\Attachment;

/**
 * Sent from the locked/unactivated license page (see
 * Admin\LicenseController::purchaseRequest) when someone who self-hosted
 * WSRetail from the public repo fills in the "buy a license" form. The
 * payment slip is attached directly from the upload's temp file, never
 * written to permanent storage on the customer's own server.
 */
class LicensePurchaseRequested extends Mailable
{
    use Queueable;

    public function __construct(
        public array $details,
        public ?UploadedFile $slip,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'WSRetail license purchase request - ' . ($this->details['business_name'] ?: $this->details['name']),
            replyTo: $this->details['email'] ? [$this->details['email']] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.license-purchase-requested',
            with: ['details' => $this->details],
        );
    }

    public function attachments(): array
    {
        if (!$this->slip) {
            return [];
        }

        return [
            Attachment::fromPath($this->slip->getRealPath())
                ->as('payment-slip-' . $this->slip->getClientOriginalName())
                ->withMime($this->slip->getMimeType()),
        ];
    }
}
