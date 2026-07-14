<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
     * Live ↔ Local bidirectional sync settings.
     *
     * LOCAL DEV ONLY — leave LIVE_APP_URL empty on production.
     *
     * LIVE_APP_URL      — base URL to pull data FROM (local → live). Unset on production.
     * SYNC_PULL_ENABLED — allow sync:pull + auto-pull on admin navigation (local dev only).
     * SYNC_SECRET       — shared secret validated by X-Sync-Secret header.
     * SYNC_PEER_URL     — base URL of the OTHER instance that receives push events.
     */
    'sync' => [
        'live_url'    => env('LIVE_APP_URL'),
        'pull_enabled' => (bool) env('SYNC_PULL_ENABLED', false),
        'secret'      => env('SYNC_SECRET'),
        'peer_url'    => env('SYNC_PEER_URL'),
    ],

];
