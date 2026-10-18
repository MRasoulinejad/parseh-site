<?php
// اجرای روزانه از Cron دایرکت‌ادمین، مثلاً هر روز ساعت ۱۰ صبح:
// /usr/local/bin/php /home/USERNAME/parseh_app/cron.php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require __DIR__ . '/bootstrap.php';

$out = [];

// ۱. یادآوری نوبت‌های فردا
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$rows = db_all(
    "SELECT a.id, a.starts_at, p.mobile, s.title, s.prep_text FROM appointments a
     JOIN patients p ON p.id = a.patient_id JOIN services s ON s.id = a.service_id
     WHERE a.starts_at >= ? AND a.starts_at < ? AND a.status = 'confirmed' AND a.reminded_at IS NULL",
    [$tomorrow . ' 00:00:00', date('Y-m-d', strtotime('+2 day')) . ' 00:00:00']
);
foreach ($rows as $r) {
    sms_send($r['mobile'], 'reminder',
        "یادآوری نوبت فردا در رادیولوژی پارسه:\n{$r['title']}\nساعت " . fa_num(jdate($r['starts_at'], 'time')) .
        ($r['prep_text'] ? "\nآمادگی: {$r['prep_text']}" : ''),
        ['SERVICE' => $r['title'], 'TIME' => fa_num(jdate($r['starts_at'], 'time'))]);
    db_update('appointments', ['reminded_at' => now()], 'id = ?', [$r['id']]);
}
$out[] = 'reminders: ' . count($rows);

// ۲. حذف جواب‌هایی که مهلت نگهداری‌شان تمام شده
$expired = db_all("SELECT * FROM results WHERE status = 'ready' AND expires_at IS NOT NULL AND expires_at < ?", [now()]);
foreach ($expired as $r) {
    delete_private($r['report_path']);
    foreach (db_all('SELECT file_path FROM result_images WHERE result_id = ?', [$r['id']]) as $img) {
        delete_private($img['file_path']);
    }
    db_query('DELETE FROM result_images WHERE result_id = ?', [$r['id']]);
    db_update('results', ['status' => 'expired', 'report_path' => null], 'id = ?', [$r['id']]);
}
$out[] = 'expired results: ' . count($expired);

// ۳. پاک‌سازی
db_query('DELETE FROM otp_codes WHERE expires_at < ?', [date('Y-m-d H:i:s', strtotime('-1 day'))]);
db_query('DELETE FROM rate_limits WHERE reset_at < ?', [now()]);
db_query('DELETE FROM sms_logs WHERE created_at < ?', [date('Y-m-d H:i:s', strtotime('-1 year'))]);

echo '[' . now() . '] ' . implode(', ', $out) . PHP_EOL;
