<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TransferReceivedEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $userName,
        public readonly string $amount,
        public readonly string $senderEmail,
        public readonly string $newBalance
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@walletapi.com', 'Wallet API'),
            subject: 'Você recebeu uma transferência!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transfer-received',
        );
    }
}
