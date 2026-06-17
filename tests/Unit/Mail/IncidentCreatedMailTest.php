<?php

use Cachet\Mail\IncidentCreatedMail;
use Cachet\Models\Incident;
use Cachet\Models\Subscriber;

it('renders the incident created mail with the incident name in the subject', function () {
    $subscriber = Subscriber::factory()->verified()->create();
    $incident = Incident::factory()->create(['name' => 'Database Outage']);

    $mail = new IncidentCreatedMail($incident, $subscriber);

    $mail->assertHasSubject(__('cachet::notification.incident.created.subject', ['name' => 'Database Outage']));
});

it('includes the incident name and an unsubscribe link', function () {
    $subscriber = Subscriber::factory()->verified()->create();
    $incident = Incident::factory()->create(['name' => 'Database Outage']);

    $mail = new IncidentCreatedMail($incident, $subscriber);

    $mail->assertSeeInHtml('Database Outage');
    $mail->assertSeeInHtml(route('cachet.subscriber.manage', $subscriber->verify_code), false);
});
