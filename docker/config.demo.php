<?php
// تنظیمات نسخه نمایشی (Railway). روی هاست اصلی از config.sample.php استفاده کنید.
return [
    'app_name' => 'رادیولوژی پارسه',
    'app_url'  => getenv('APP_URL') ?: 'http://localhost:8080',
    'debug'    => false,
    'demo'     => true,
    'timezone' => 'Asia/Tehran',
    'db' => [
        'dsn'  => 'sqlite:' . (getenv('DATA_DIR') ?: '/app/data') . '/parseh.db',
        'user' => null,
        'pass' => null,
    ],
    'sms' => ['driver' => 'log', 'kavenegar_key' => '', 'sender' => ''],
    'result_retention_days' => 90,
    'booking_days_ahead'    => 14,
    'cancel_hours_before'   => 3,
    'public_uploads_dir' => __DIR__ . '/../public_html/uploads',
    'public_uploads_url' => '/uploads',
];
