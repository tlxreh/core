<?php

use Cachet\Events\Subscribers\SubscriberCreated;
use Cachet\Listeners\SendVerificationEmailListener;
use Cachet\Mail\SubscriberVerificationMail;
use Cachet\Models\Subscriber;
use Cachet\Settings\AppSettings;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Event::fake();
    Mail::fake();
});

function verificationListener(bool $enabled = true): SendVerificationEmailListener
{
    $settings = app(AppSettings::class);
    $settings->subscriber_notifications_enabled = $enabled;

    return new SendVerificationEmailListener($settings);
}

it('sends a verification email when an unverified subscriber is created', function () {
    Mail::fake();

    $subscriber = Subscriber::factory()->create();

    verificationListener()->handle(new SubscriberCreated($subscriber));

    Mail::assertQueued(SubscriberVerificationMail::class, fn (SubscriberVerificationMail $mail) => $mail->hasTo($subscriber->email));
});

it('does not send a verification email to an already verified subscriber', function () {
    Mail::fake();

    $subscriber = Subscriber::factory()->verified()->create();

    verificationListener()->handle(new SubscriberCreated($subscriber));

    Mail::assertNothingQueued();
});

it('does not send a verification email when notifications are disabled', function () {
    Mail::fake();

    $subscriber = Subscriber::factory()->create();

    verificationListener(enabled: false)->handle(new SubscriberCreated($subscriber));

    Mail::assertNothingQueued();
});
