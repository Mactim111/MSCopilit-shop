<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderManager extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $order_id,
        public string $note = '' // Сюда передадим адрес или примечание
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Новый заказ #{$this->order_id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.order-manager',
        );
    }
}