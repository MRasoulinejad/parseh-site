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
    return view('admin/sms', ['page' => paginate('SELECT * FROM sms_logs ORDER BY id DESC', [], 50), 'title' => 'پیامک‌های ارسالی'], 'admin');
}
