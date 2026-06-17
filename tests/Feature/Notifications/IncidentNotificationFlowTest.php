<?php

use Cachet\Actions\Incident\CreateIncident;
use Cachet\Data\Requests\Incident\CreateIncidentRequestData;
use Cachet\Enums\ComponentStatusEnum;
use Cachet\Enums\IncidentStatusEnum;
use Cachet\Mail\IncidentCreatedMail;
use Cachet\Models\Component;
use Cachet\Models\Subscriber;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\Mail;

/**
 * Flush deferred callbacks the way the framework does after a response is sent.
 */
function flushDeferredCallbacks(): void
{
    app(DeferredCallbackCollection::class)->invoke();
}

it('notifies component subscribers when an incident is created through the action', function () {
    Mail::fake();

    $component = Component::factory()->create();
    $other = Component::factory()->create();

    $subscriber = Subscriber::factory()->verified()->create(['email' => 'related@example.com', 'global' => false]);
    $subscriber->components()->attach($component->id);

    $unrelated = Subscriber::factory()->verified()->create(['email' => 'unrelated@example.com', 'global' => false]);
    $unrelated->components()->attach($other->id);

    app(CreateIncident::class)->handle(CreateIncidentRequestData::from([
        'name' => 'Database Outage',
        'message' => 'We are investigating.',
        'status' => IncidentStatusEnum::investigating,
        'notifications' => true,
        'components' => [
            ['id' => $component->id, 'status' => ComponentStatusEnum::major_outage->value],
        ],
    ]));

    // Components are attached after the "created" event fires, so the notification
    // is deferred. Nothing should be queued until the deferred callbacks run.
    Mail::assertNotQueued(IncidentCreatedMail::class);

    flushDeferredCallbacks();

    Mail::assertQueued(IncidentCreatedMail::class, fn (IncidentCreatedMail $mail) => $mail->hasTo($subscriber->email));
    Mail::assertNotQueued(IncidentCreatedMail::class, fn (IncidentCreatedMail $mail) => $mail->hasTo($unrelated->email));
});

it('does not notify component subscribers when the incident has notifications disabled', function () {
    Mail::fake();

    $component = Component::factory()->create();
    $subscriber = Subscriber::factory()->verified()->create(['global' => false]);
    $subscriber->components()->attach($component->id);

    app(CreateIncident::class)->handle(CreateIncidentRequestData::from([
        'name' => 'Silent Incident',
        'message' => 'No notifications.',
        'status' => IncidentStatusEnum::investigating,
        'notifications' => false,
        'components' => [
            ['id' => $component->id, 'status' => ComponentStatusEnum::major_outage->value],
        ],
    ]));

    flushDeferredCallbacks();

    Mail::assertNotQueued(IncidentCreatedMail::class);
});
