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
     * SYNC_SECRET  — shared secret validated by X-Sync-Secret header.
     *               Must be identical on both instances.
     * SYNC_PEER_URL — base URL of the OTHER instance that should receive
     *               change events from this one.
     *               e.g. on local:  SYNC_PEER_URL=https://tgworld.e-saloon.online
     *                    on live:   SYNC_PEER_URL=https://<your-cloudflare-tunnel>.trycloudflare.com
     */
    'sync' => [
        'live_url' => env('LIVE_APP_URL', 'https://tgworld.e-saloon.online'),
        'secret'   => env('SYNC_SECRET'),
        'peer_url' => env('SYNC_PEER_URL'),
    ],

];
