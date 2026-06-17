<?php

namespace Cachet\Events\Incidents;

use Cachet\Concerns\SendsWebhook;
use Cachet\Enums\WebhookEventEnum;
use Cachet\Models\Incident;
use Cachet\Models\Update;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentUpdateCreated
{
    use Dispatchable, InteractsWithSockets, SendsWebhook, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Incident $incident, public Update $update)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }

    public function getWebhookPayload(): array
    {
        return $this->update->toArray();
    }

    public function getWebhookEventName(): WebhookEventEnum
    {
        return WebhookEventEnum::incident_update_created;
    }
}
