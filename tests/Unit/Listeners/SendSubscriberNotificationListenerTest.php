<?php

use Cachet\Enums\IncidentStatusEnum;
use Cachet\Events\Incidents\IncidentCreated;
use Cachet\Events\Incidents\IncidentUpdateCreated;
use Cachet\Listeners\SendSubscriberNotificationListener;
use Cachet\Mail\IncidentCreatedMail;
use Cachet\Mail\IncidentUpdatedMail;
use Cachet\Models\Component;
use Cachet\Models\Incident;
use Cachet\Models\Subscriber;
use Cachet\Models\Update;
use Cachet\Settings\AppSettings;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Event::fake();
    Mail::fake();
    $this->withoutDefer();
});

function listenerWithNotifications(bool $enabled = true): SendSubscriberNotificationListener
{
    $settings = app(AppSettings::class);
    $settings->subscriber_notifications_enabled = $enabled;

    return new SendSubscriberNotificationListener($settings);
}

it('notifies global verified subscribers when an incident is created', function () {
    Mail::fake();

    $subscriber = Subscriber::factory()->verified()->create(['global' => true]);

    $incident = Incident::factory()->create(['notifications' => true]);

    listenerWithNotifications()->handle(IncidentCreated::class, [new IncidentCreated($incident)]);

    Mail::assertQueued(IncidentCreatedMail::class, fn (IncidentCreatedMail $mail) => $mail->hasTo($subscriber->email));
});

it('notifies subscribers of affected components', function () {
    Mail::fake();

    $component = Component::factory()->create();
    $other = Component::factory()->create();

    $subscriber = Subscriber::factory()->verified()->create(['email' => 'related@example.com', 'global' => false]);
    $subscriber->components()->attach($component->id);

    $unrelated = Subscriber::factory()->verified()->create(['email' => 'unrelated@example.com', 'global' => false]);
    $unrelated->components()->attach($other->id);

    $incident = Incident::factory()->create(['notifications' => true]);
    $incident->components()->attach($component->id, ['component_status' => 1]);

    listenerWithNotifications()->handle(IncidentCreated::class, [new IncidentCreated($incident)]);

    Mail::assertQueued(IncidentCreatedMail::class, fn (IncidentCreatedMail $mail) => $mail->hasTo($subscriber->email));
    Mail::assertNotQueued(IncidentCreatedMail::class, fn (IncidentCreatedMail $mail) => $mail->hasTo($unrelated->email));
});

it('does not notify unverified subscribers', function () {
    Mail::fake();

    Subscriber::factory()->create(['global' => true]);

    $incident = Incident::factory()->create(['notifications' => true]);

    listenerWithNotifications()->handle(IncidentCreated::class, [new IncidentCreated($incident)]);

    Mail::assertNothingQueued();
});

it('does not notify when the incident has notifications disabled', function () {
    Mail::fake();

    Subscriber::factory()->verified()->create(['global' => true]);

    $incident = Incident::factory()->create(['notifications' => false]);

    listenerWithNotifications()->handle(IncidentCreated::class, [new IncidentCreated($incident)]);

    Mail::assertNothingQueued();
});

it('does not notify when subscriber notifications are disabled in settings', function () {
    Mail::fake();

    Subscriber::factory()->verified()->create(['global' => true]);

    $incident = Incident::factory()->create(['notifications' => true]);

    listenerWithNotifications(enabled: false)->handle(IncidentCreated::class, [new IncidentCreated($incident)]);

    Mail::assertNothingQueued();
});

it('sends an updated mail when an incident update is created', function () {
    Mail::fake();

    $subscriber = Subscriber::factory()->verified()->create(['global' => true]);

    $incident = Incident::factory()->create(['notifications' => true]);
    $update = new Update(['message' => 'Working on it', 'status' => IncidentStatusEnum::investigating]);
    $incident->updates()->save($update);

    listenerWithNotifications()->handle(IncidentUpdateCreated::class, [new IncidentUpdateCreated($incident, $update)]);

    Mail::assertQueued(IncidentUpdatedMail::class, fn (IncidentUpdatedMail $mail) => $mail->hasTo($subscriber->email));
});
