<?php

use Cachet\Actions\Update\CreateUpdate;
use Cachet\Data\Requests\IncidentUpdate\CreateIncidentUpdateRequestData;
use Cachet\Data\Requests\ScheduleUpdate\CreateScheduleUpdateRequestData;
use Cachet\Enums\ComponentStatusEnum;
use Cachet\Enums\IncidentStatusEnum;
use Cachet\Events\Incidents\IncidentUpdateCreated;
use Cachet\Models\Component;
use Cachet\Models\Incident;
use Cachet\Models\Schedule;
use Illuminate\Support\Facades\Event;

it('can create an incident update', function () {
    $incident = Incident::factory()->create();

    $data = CreateIncidentUpdateRequestData::from([
        'message' => 'This is an update message.',
        'status' => IncidentStatusEnum::investigating,
    ]);

    $incidentUpdate = app(CreateUpdate::class)->handle($incident, $data);

    expect($incidentUpdate)
        ->message->toBe($data->message);
});

it('an incident\'s computed latest status equals the new status', function () {
    $incident = Incident::factory()->create([
        'status' => IncidentStatusEnum::investigating,
    ]);

    $data = CreateIncidentUpdateRequestData::from([
        'message' => 'This is an update message.',
        'status' => IncidentStatusEnum::identified,
    ]);

    $incidentUpdate = app(CreateUpdate::class)->handle($incident, $data);

    expect($incidentUpdate)
        ->message->toBe($data->message)
        ->and($incident->fresh())
        ->latestStatus->toEqual(IncidentStatusEnum::identified);
});

it('sets linked component status to operational when incident update status is fixed', function () {
    $incident = Incident::factory()->create([
        'status' => IncidentStatusEnum::investigating,
    ]);

    $component = Component::factory()->create([
        'status' => ComponentStatusEnum::operational,
    ]);

    $incident->components()->attach($component->id, [
        'component_status' => ComponentStatusEnum::major_outage,
    ]);

    $data = CreateIncidentUpdateRequestData::from([
        'message' => 'This issue has been fixed.',
        'status' => IncidentStatusEnum::fixed,
    ]);

    app(CreateUpdate::class)->handle($incident, $data);

    expect($incident->components()->first()->pivot->component_status)
        ->toEqual(ComponentStatusEnum::operational);
});

it('can create a schedule update', function () {
    $schedule = Schedule::factory()->create();

    $data = CreateScheduleUpdateRequestData::from([
        'message' => 'This is an update message for a schedule.',
        'status' => IncidentStatusEnum::investigating,
    ]);

    $incidentUpdate = app(CreateUpdate::class)->handle($schedule, $data);

    expect($incidentUpdate)
        ->message->toBe($data->message);
});

it('dispatches IncidentUpdateCreated when updating an incident', function () {
    Event::fake();

    $incident = Incident::factory()->create();

    $data = CreateIncidentUpdateRequestData::from([
        'message' => 'This is an update message.',
        'status' => IncidentStatusEnum::investigating,
    ]);

    $update = app(CreateUpdate::class)->handle($incident, $data);

    Event::assertDispatched(IncidentUpdateCreated::class, function (IncidentUpdateCreated $event) use ($incident, $update) {
        return $event->incident->is($incident) && $event->update->is($update);
    });
});

it('does not dispatch IncidentUpdateCreated when updating a schedule', function () {
    Event::fake();

    $schedule = Schedule::factory()->create();

    $data = CreateScheduleUpdateRequestData::from([
        'message' => 'This is an update message for a schedule.',
        'status' => IncidentStatusEnum::investigating,
    ]);

    app(CreateUpdate::class)->handle($schedule, $data);

    Event::assertNotDispatched(IncidentUpdateCreated::class);
});
