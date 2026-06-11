<?php

return [
    'default' => env('SMS_PROVIDER', 'log'),

    'providers' => [
        'log' => [
            'driver' => 'log',
        ],

        'hubtel' => [
            'driver' => 'hubtel',
            'api_key' => env('HUBTEL_API_KEY'),
            'api_secret' => env('HUBTEL_API_SECRET'),
            'sender_id' => env('HUBTEL_SENDER_ID', 'SCHOOL'),
        ],

        'mnotify' => [
            'driver' => 'mnotify',
            'api_key' => env('MNOTIFY_API_KEY'),
            'sender_id' => env('MNOTIFY_SENDER_ID', 'SCHOOL'),
        ],

        'twilio' => [
            'driver' => 'twilio',
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
    ],

    'auto_send' => [
        'payment_confirmation' => env('SMS_AUTO_PAYMENT_CONFIRM', false),
        'balance_reminders' => env('SMS_AUTO_BALANCE_REMINDERS', false),
    ],
];
