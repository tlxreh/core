<?php

return [
    'resource_label' => 'Subscriber|Subscribers',
    'list' => [
        'headers' => [
            'email' => 'Email',
            'verify_code' => 'Verify code',
            'global' => 'Global',
            'phone_number' => 'Phone number',
            'slack_webhook_url' => 'Slack Webhook URL',
            'verified_at' => 'Verified at',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
        'empty_state' => [
            'heading' => 'Subscribers',
            'description' => 'Subscribers are people who have subscribed to your status page for notifications.',
        ],
        'actions' => [
            'verify_label' => 'Verify',
        ],
    ],
    'form' => [
        'email_label' => 'Email',
        'verify_code_label' => 'Verify code',
        'verified_at_label' => 'Verified at',
        'global_label' => 'Global',
    ],
    'overview' => [
        'total_subscribers_label' => 'Total Subscribers',
        'total_subscribers_description' => 'Total number of subscribers.',
    ],
    'subscribe' => [
        'title' => 'Subscribe to Updates',
        'description' => 'Get notified by email when incidents are created, updated, or resolved.',
        'email_label' => 'Email Address',
        'global_label' => 'Notify me about all components',
        'components_label' => 'Or choose specific components',
        'submit' => 'Subscribe',
    ],
    'manage' => [
        'title' => 'Manage Your Subscription',
        'description' => 'Update which components you want to be notified about, or unsubscribe entirely.',
        'update_submit' => 'Update Preferences',
        'unsubscribe_submit' => 'Unsubscribe',
    ],
    'flash' => [
        'subscribed' => 'Thanks for subscribing! Please check your email to verify your subscription.',
        'verified' => 'Your subscription has been verified.',
        'updated' => 'Your subscription preferences have been updated.',
        'unsubscribed' => 'You have been unsubscribed.',
    ],
];
