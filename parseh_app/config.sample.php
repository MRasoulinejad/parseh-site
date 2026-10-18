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

    // پیامک: driver = smsir | log (فقط ثبت در لاگ، برای تست) | kavenegar
    'sms' => [
        'driver' => 'log',

        // ---- SMS.ir ----
        // کلید API: پنل sms.ir → برنامه‌نویسان → لیست کلیدهای API
        'smsir_api_key' => '',
        // شماره خط اختصاصی پنل (برای پیام‌هایی که قالب ندارند)
        'smsir_line'    => '',
        // شناسه قالب‌های «ارسال سریع» (پنل sms.ir → ارسال سریع → قالب‌ها). صفر = بدون قالب (ارسال متن کامل از خط)
        // نام پارامترها در قالب باید دقیقاً همین‌ها باشد (راهنمای نصب، بخش پیامک)
        'smsir_templates' => [
            'otp'          => 0, // پارامتر: CODE
            'confirm'      => 0, // پارامترها: SERVICE، DATE، TIME، CODE
            'reminder'     => 0, // پارامترها: SERVICE، TIME
            'result_ready' => 0, // پارامتر: CODE
            'test'         => 0, // اختیاری
        ],

        // ---- کاوه‌نگار (اختیاری) ----
        'kavenegar_key' => '',
        'sender'        => '',
    ],

    // ایمیل (اطلاع‌رسانی پاسخ پیام‌های فرم تماس)
    // در دایرکت‌ادمین: E-Mail Accounts → یک حساب مثل no-reply@دامنه بسازید
    'mail' => [
        'driver'       => 'log',          // smtp | mail | log
        'host'         => 'mail.parsehradiology.ir',
        'port'         => 465,            // 465 با ssl یا 587 با tls
        'encryption'   => 'ssl',
        'username'     => 'no-reply@parsehradiology.ir',
        'password'     => '',
        'from_address' => 'no-reply@parsehradiology.ir',
        'from_name'    => 'رادیولوژی پارسه',
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
