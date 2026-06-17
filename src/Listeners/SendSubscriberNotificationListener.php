<?php

namespace Cachet\Listeners;

use Cachet\Events\Incidents\IncidentCreated;
use Cachet\Events\Incidents\IncidentUpdateCreated;
use Cachet\Mail\IncidentCreatedMail;
use Cachet\Mail\IncidentUpdatedMail;
use Cachet\Models\Incident;
use Cachet\Models\Subscriber;
use Cachet\Settings\AppSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class SendSubscriberNotificationListener
{
    public function __construct(private AppSettings $appSettings) {}

    /**
     * Handle incident notification events.
     *
     * @param  array<int, mixed>  $data
     */
    public function handle(string $eventName, array $data): void
    {
        if (! $this->appSettings->subscriber_notifications_enabled) {
            return;
        }

        [$event] = $data;

        if ($event instanceof IncidentCreated) {
            $this->notifyIncidentCreated($event);
        } elseif ($event instanceof IncidentUpdateCreated) {
            $this->notifyIncidentUpdated($event);
        }
    }

    /**
     * Notify subscribers that an incident was created.
     *
     * The notification is deferred until after the response is sent because the
     * incident's components are attached after the "created" event fires (both
     * via the dashboard's Filament relationship and the CreateIncident action),
     * so querying them synchronously would miss component subscribers.
     */
    private function notifyIncidentCreated(IncidentCreated $event): void
    {
        $incident = $event->incident;

        if (! $incident->notifications) {
            return;
        }

        defer(function () use ($incident) {
            foreach ($this->subscribersForIncident($incident) as $subscriber) {
                Mail::to($subscriber->email)->queue(new IncidentCreatedMail($incident, $subscriber));
            }
        });
    }

    /**
     * Notify subscribers that an incident was updated.
     */
    private function notifyIncidentUpdated(IncidentUpdateCreated $event): void
    {
        $incident = $event->incident;

        if (! $incident->notifications) {
            return;
        }

        foreach ($this->subscribersForIncident($incident) as $subscriber) {
            Mail::to($subscriber->email)->queue(new IncidentUpdatedMail($incident, $event->update, $subscriber));
        }
    }

    /**
     * Get the verified subscribers that should be notified about the incident.
     *
     * @return Collection<int, Subscriber>
     */
    private function subscribersForIncident(Incident $incident): Collection
    {
        $componentIds = $incident->components()->pluck('components.id')->all();

        return Subscriber::query()
            ->whereNotNull('verified_at')
            ->whereNotNull('email')
            ->where(function (Builder $query) use ($componentIds) {
                $query->where('global', true);

                if ($componentIds) {
                    $query->orWhereHas('components', fn (Builder $components) => $components->whereIn('components.id', $componentIds));
                }
            })
            ->get();
    }
}
