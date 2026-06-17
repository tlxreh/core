<?php

use Cachet\Mail\SubscriberVerificationMail;
use Cachet\Models\Component;
use Cachet\Models\Subscriber;
use Cachet\Settings\AppSettings;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->withoutVite();
});

it('shows the subscribe form', function () {
    $this->get(route('cachet.subscriber.subscribe'))
        ->assertOk()
        ->assertSee(__('cachet::subscriber.subscribe.title'));
});

it('can subscribe with an email and queues a verification mail', function () {
    Mail::fake();

    $this->post(route('cachet.subscriber.subscribe.store'), [
        'email' => 'jane@example.com',
        'global' => '1',
    ])->assertRedirect(route('cachet.subscriber.subscribe'));

    $subscriber = Subscriber::query()->where('email', 'jane@example.com')->first();

    expect($subscriber)->not->toBeNull()
        ->and((bool) $subscriber->global)->toBeTrue()
        ->and($subscriber->verified_at)->toBeNull();

    Mail::assertQueued(SubscriberVerificationMail::class);
});

it('can subscribe to specific components', function () {
    Mail::fake();

    $component = Component::factory()->create();

    $this->post(route('cachet.subscriber.subscribe.store'), [
        'email' => 'jane@example.com',
        'components' => [$component->id],
    ])->assertRedirect();

    $subscriber = Subscriber::query()->where('email', 'jane@example.com')->first();

    expect($subscriber->components)->toHaveCount(1);
});

it('validates the email when subscribing', function () {
    $this->post(route('cachet.subscriber.subscribe.store'), [
        'email' => 'not-an-email',
    ])->assertSessionHasErrors('email');
});

it('handles duplicate email subscriptions gracefully', function () {
    Mail::fake();

    Subscriber::factory()->create(['email' => 'jane@example.com']);

    $this->post(route('cachet.subscriber.subscribe.store'), [
        'email' => 'jane@example.com',
        'global' => '1',
    ])->assertRedirect();

    expect(Subscriber::query()->where('email', 'jane@example.com')->count())->toBe(1);
});

it('can verify a subscriber', function () {
    $subscriber = Subscriber::factory()->create();

    $this->get(route('cachet.subscriber.verify', $subscriber->verify_code))
        ->assertRedirect(route('cachet.subscriber.manage', $subscriber->verify_code));

    expect($subscriber->fresh()->verified_at)->not->toBeNull();
});

it('returns 404 verifying an unknown code', function () {
    $this->get(route('cachet.subscriber.verify', 'unknown-code'))->assertNotFound();
});

it('shows the manage form', function () {
    $subscriber = Subscriber::factory()->verified()->create();

    $this->get(route('cachet.subscriber.manage', $subscriber->verify_code))
        ->assertOk()
        ->assertSee($subscriber->email);
});

it('can update subscription preferences', function () {
    $subscriber = Subscriber::factory()->verified()->create(['global' => false]);
    $component = Component::factory()->create();

    $this->put(route('cachet.subscriber.manage.update', $subscriber->verify_code), [
        'components' => [$component->id],
    ])->assertRedirect(route('cachet.subscriber.manage', $subscriber->verify_code));

    expect($subscriber->fresh()->components)->toHaveCount(1);
});

it('can unsubscribe', function () {
    $subscriber = Subscriber::factory()->verified()->create();

    $this->delete(route('cachet.subscriber.unsubscribe', $subscriber->verify_code))
        ->assertRedirect(route('cachet.status-page'));

    expect(Subscriber::query()->where('verify_code', $subscriber->verify_code)->exists())->toBeFalse();
});

it('rate limits the subscribe endpoint', function () {
    Mail::fake();

    for ($i = 0; $i <= 5; $i++) {
        $response = $this->post(route('cachet.subscriber.subscribe.store'), [
            'email' => "user{$i}@example.com",
            'global' => '1',
        ]);

        if ($i < 5) {
            $response->assertRedirect();
        } else {
            $response->assertStatus(429);
        }
    }
});

it('returns 404 when subscriber notifications are disabled', function () {
    $settings = app(AppSettings::class);
    $settings->subscriber_notifications_enabled = false;
    $settings->save();

    $this->get(route('cachet.subscriber.subscribe'))->assertNotFound();
});
