<?php

namespace Cachet\Listeners;

use Cachet\Events\Subscribers\SubscriberCreated;
use Cachet\Mail\SubscriberVerificationMail;
use Cachet\Settings\AppSettings;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmailListener
{
    public function __construct(private AppSettings $appSettings) {}

    /**
     * Handle the subscriber created event.
     */
    public function handle(SubscriberCreated $event): void
    {
        if (! $this->appSettings->subscriber_notifications_enabled) {
            return;
        }

        $subscriber = $event->subscriber;

        if ($subscriber->verified_at || ! $subscriber->email) {
            return;
        }

        Mail::to($subscriber->email)->queue(new SubscriberVerificationMail($subscriber));
    }
}
