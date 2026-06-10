<?php

namespace App\Mail;

use App\Models\CaseRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCaseIntake extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CaseRecord $case) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Case Registered — {$this->case->case_uid} · {$this->case->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-case-intake',
        );
    }
}
