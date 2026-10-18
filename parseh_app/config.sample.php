<?php
// این فایل را به نام config.php کپی کنید و مقادیر را پر کنید.
return [
    'app_name' => 'رادیولوژی پارسه',
    // آدرس کامل سایت بدون / در انتها
    'app_url'  => 'https://parsehradiology.ir',
    // در حالت عادی false بماند؛ فقط برای عیب‌یابی true کنید
    'debug'    => false,
    'timezone' => 'Asia/Tehran',

    // اطلاعات دیتابیس از بخش MySQL Management دایرکت‌ادمین
    'db' => [
        'dsn'  => 'mysql:host=localhost;dbname=USERNAME_parseh;charset=utf8mb4',
        'user' => 'USERNAME_parseh',
        'pass' => 'DB_PASSWORD',
    ],

    // پیامک: driver = log (فقط ثبت در لاگ، برای تست) یا kavenegar
    'sms' => [
        'driver'        => 'log',
        'kavenegar_key' => '',
        'sender'        => '',
    ],

    // تعداد روزی که جواب‌ها روی سایت می‌مانند
    'result_retention_days' => 90,
    // تا چند روز آینده نوبت آنلاین قابل رزرو است
    'booking_days_ahead'    => 14,
    // تا چند ساعت قبل از نوبت، بیمار می‌تواند لغو کند
    'cancel_hours_before'   => 3,

    // پوشه فایل‌های عمومی (گالری، لوگو بیمه، تصویر مقاله) داخل public_html
    'public_uploads_dir' => __DIR__ . '/../public_html/uploads',
    'public_uploads_url' => '/uploads',
];
