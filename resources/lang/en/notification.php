<?php

return [
    'thanks' => 'Thanks',
    'subscriber' => [
        'verify' => [
            'subject' => 'Verify Your Subscription',
            'heading' => 'Confirm Your Subscription',
            'body' => 'You are receiving this email because you subscribed to status updates. Please confirm your subscription by clicking the button below.',
            'button' => 'Verify Subscription',
            'ignore' => 'If you did not request this subscription, no further action is required.',
        ],
    ],
    'incident' => [
        'status' => 'Status',
        'view_button' => 'View Incident',
        'created' => [
            'subject' => 'New Incident: :name',
            'intro' => 'A new incident has been reported affecting services you are subscribed to.',
        ],
        'updated' => [
            'subject' => 'Incident Updated: :name',
            'intro' => 'An incident you are subscribed to has been updated.',
        ],
        'resolved' => [
            'subject' => 'Incident Resolved: :name',
            'intro' => 'An incident you are subscribed to has been resolved.',
        ],
    ],
    'unsubscribe' => [
        'text' => 'You are receiving this email because you subscribed to status updates.',
        'link' => 'Manage your subscription',
    ],
];
