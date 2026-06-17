<?php

namespace Cachet\Mail;

use Cachet\Enums\IncidentStatusEnum;
use Cachet\Models\Incident;
use Cachet\Models\Subscriber;
use Cachet\Models\Update;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidentUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Incident $incident, public Update $update, public Subscriber $subscriber)
    {
        $this->onConnection(config('cachet.notifications.queue_connection'));
        $this->onQueue(config('cachet.notifications.queue_name'));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $key = $this->update->status === IncidentStatusEnum::fixed
            ? 'cachet::notification.incident.resolved.subject'
            : 'cachet::notification.incident.updated.subject';

        return new Envelope(
            subject: __($key, ['name' => $this->incident->name]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'cachet::mail.incident.updated',
            with: [
                'incident' => $this->incident,
                'update' => $this->update,
                'resolved' => $this->update->status === IncidentStatusEnum::fixed,
                'incidentUrl' => route('cachet.status-page.incident', $this->incident->guid),
                'manageUrl' => route('cachet.subscriber.manage', $this->subscriber->verify_code),
            ],
        );
    }
}
