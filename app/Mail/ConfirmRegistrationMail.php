<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public string $token, string $locale = 'fr')
    {
        $this->token = $token;
        $this->locale = $locale; // OK — sans la déclarer
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Active la langue du mail avant rendu
        app()->setLocale($this->locale);

        return new Envelope(
            subject: __('messages.email.register.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'email.ConfirmRegistration', // ⬅️ Markdown et pas view:
            with: [
                'token' => $this->token,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
