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
    |--------------------------------------------------------------------------
    | Telegram Bot — LARAS Alert System
    |--------------------------------------------------------------------------
    | Bot Token didapat dari @BotFather di Telegram.
    | Chat ID bisa berupa ID personal, grup, atau channel.
    */
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
        'chat_id'   => env('TELEGRAM_CHAT_ID', ''),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
    ],

     'n8n' => [
        'token' => env('N8N_TOKEN', ''),
    ],
        'gowa' => [
        // false = notifikasi WA otomatis dari web dimatikan (mis. di Vercel, karena
        // peringatan WA sudah ditangani workflow n8n). Kirim manual tetap bisa dicoba.
        'enabled'   => (bool) env('LARAS_WA_LARAVEL', true),
        'url'       => env('GOWA_URL', ''),
        'user'      => env('GOWA_USER', ''),
        'pass'      => env('GOWA_PASS', ''),
        'device_id' => env('GOWA_DEVICE_ID', ''),
        // Nomor/grup yang menerima peringatan SEMUA desa (mis. grup BPBD). Nomor warga
        // per desa diisi di halaman /verifikasi-peringatan (tabel warga_contacts).
        'targets'   => env('WA_ALERT_TARGETS', ''),
        // Nomor kader tambahan (selain kolom phone akun admin/kader yang aktif)
        'kader_targets'          => env('WA_KADER_TARGETS', ''),
        // Data node lebih tua dari N menit dianggap mati, tidak memicu peringatan
        'fresh_minutes'          => env('WA_FRESH_MINUTES', 30),
        'auto_send_minutes'      => env('WA_BAHAYA_AUTO_SEND_MINUTES', 10),
        'waspada_expire_minutes' => env('WA_WASPADA_EXPIRE_MINUTES', 30),
    ],

];