<?php

namespace Cachet\Mail;

use Cachet\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriberVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Subscriber $subscriber)
    {
        $this->onConnection(config('cachet.notifications.queue_connection'));
        $this->onQueue(config('cachet.notifications.queue_name'));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('cachet::notification.subscriber.verify.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'cachet::mail.subscriber.verify',
            with: [
                'verifyUrl' => route('cachet.subscriber.verify', $this->subscriber->verify_code),
            ],
        );
    }
}
