<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewMessage extends Mailable
{
    public function __construct(public Message $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Portfolio enquiry from {$this->message->name}",
            replyTo: [new Address($this->message->email, $this->message->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.new-message', with: [
            'm' => $this->message,
            'type' => Message::PROJECT_TYPES[$this->message->project_type] ?? null,
            'budget' => Message::BUDGETS[$this->message->budget] ?? null,
        ]);
    }
}
