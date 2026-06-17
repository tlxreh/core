<?php

use Cachet\Mail\SubscriberVerificationMail;
use Cachet\Models\Subscriber;

it('renders the verification mail with the correct subject', function () {
    $subscriber = Subscriber::factory()->create();

    $mail = new SubscriberVerificationMail($subscriber);

    $mail->assertHasSubject(__('cachet::notification.subscriber.verify.subject'));
});

it('includes the verification link', function () {
    $subscriber = Subscriber::factory()->create();

    $mail = new SubscriberVerificationMail($subscriber);

    $mail->assertSeeInHtml(route('cachet.subscriber.verify', $subscriber->verify_code), false);
});
