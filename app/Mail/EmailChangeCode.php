<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Код подтверждения нового email',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.email-change-code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
