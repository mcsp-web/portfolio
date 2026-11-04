<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

class NewMessage extends Mailable
{
    public function __construct(public Message $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Portfolio] New enquiry from {$this->message->name}",
            replyTo: [new Address($this->message->email, $this->message->name)],
        );
    }

    /**
     * Marks every portfolio email so it can be filtered in the inbox and found in Resend.
     */
    public function headers(): Headers
    {
        return new Headers(text: [
            'X-Source' => 'portfolio.mcspconsulting.com',
            'X-Entity-Ref-ID' => 'portfolio-message-'.$this->message->id,
        ]);
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
