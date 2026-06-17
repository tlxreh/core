<?php

namespace Cachet\Mail;

use Cachet\Models\Incident;
use Cachet\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidentCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Incident $incident, public Subscriber $subscriber)
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
            subject: __('cachet::notification.incident.created.subject', ['name' => $this->incident->name]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'cachet::mail.incident.created',
            with: [
                'incident' => $this->incident,
                'incidentUrl' => route('cachet.status-page.incident', $this->incident->guid),
                'manageUrl' => route('cachet.subscriber.manage', $this->subscriber->verify_code),
            ],
        );
    }
}
