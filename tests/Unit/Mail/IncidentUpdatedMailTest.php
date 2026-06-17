<?php

use Cachet\Enums\IncidentStatusEnum;
use Cachet\Mail\IncidentUpdatedMail;
use Cachet\Models\Incident;
use Cachet\Models\Subscriber;
use Cachet\Models\Update;

it('renders the updated mail with the updated subject', function () {
    $subscriber = Subscriber::factory()->verified()->create();
    $incident = Incident::factory()->create(['name' => 'API Latency']);
    $update = Update::factory()->make(['status' => IncidentStatusEnum::investigating]);

    $mail = new IncidentUpdatedMail($incident, $update, $subscriber);

    $mail->assertHasSubject(__('cachet::notification.incident.updated.subject', ['name' => 'API Latency']));
});

it('renders the resolved subject when the update status is fixed', function () {
    $subscriber = Subscriber::factory()->verified()->create();
    $incident = Incident::factory()->create(['name' => 'API Latency']);
    $update = Update::factory()->make(['status' => IncidentStatusEnum::fixed]);

    $mail = new IncidentUpdatedMail($incident, $update, $subscriber);

    $mail->assertHasSubject(__('cachet::notification.incident.resolved.subject', ['name' => 'API Latency']));
});
