<?php
declare(strict_types=1);

function admin_logs(): string
{
    require_staff('logs.view');
    $page = paginate(
        'SELECT l.*, u.name AS user_name, p.first_name, p.last_name FROM audit_logs l
         LEFT JOIN users u ON u.id = l.user_id LEFT JOIN patients p ON p.id = l.patient_id ORDER BY l.id DESC',
        [],
        50
    );
    return view('admin/logs', ['page' => $page, 'title' => 'گزارش فعالیت‌ها'], 'admin');
}

function admin_sms_logs(): string
{
    require_staff('logs.view');
    return view('admin/sms', [
        'page'   => paginate('SELECT * FROM sms_logs ORDER BY id DESC', [], 50),
        'credit' => smsir_credit(),
        'title'  => 'پیامک‌ها',
    ], 'admin');
}

function admin_sms_test(): never
{
    $me = require_staff('logs.view');
    $mobile = (string) input('mobile', $me['mobile']);
    if (!valid_mobile($mobile)) {
        flash('error', 'شماره موبایل معتبر نیست.');
        redirect('/admin/sms');
    }
    if (!rate_limit('sms-test:' . $me['id'], 5, 3600)) {
        flash('error', 'حداکثر ۵ پیامک آزمایشی در ساعت.');
        redirect('/admin/sms');
    }
    $ok = sms_send($mobile, 'test', 'پیامک آزمایشی سایت رادیولوژی پارسه — اتصال سامانه پیامک برقرار است.', ['CODE' => '12345']);
    flash($ok ? 'success' : 'error', $ok ? 'پیامک آزمایشی ارسال شد.' : 'ارسال ناموفق بود. کلید API، شماره خط و قالب‌ها را در config.php بررسی کنید؛ جزئیات خطا در storage/logs/php-error.log است.');
    redirect('/admin/sms');
}
