<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JusticeHubMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject = 'Justice Hub Notification',
        public string $greeting = '',
        public string $body = '',
        public ?string $actionText = null,
        public ?string $actionUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.justice-hub');
    }

    public function attachments(): array
    {
        return [];
    }
}
