<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web Push (VAPID)
    |--------------------------------------------------------------------------
    |
    | Generate a key pair with `php artisan webpush:vapid` and put the values
    | in your .env file. Web Push requires the site to be served over HTTPS
    | (localhost is allowed by browsers during development).
    |
    */

    'subject' => env('VAPID_SUBJECT', 'mailto:admin@example.com'),

    'public_key' => env('VAPID_PUBLIC_KEY'),

    'private_key' => env('VAPID_PRIVATE_KEY'),

    'icon' => env('VAPID_ICON', '/images/notifications/icon-192.png'),

    'badge' => env('VAPID_BADGE', '/images/notifications/badge-96.png'),

];
