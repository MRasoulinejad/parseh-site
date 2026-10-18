<?php
// فقط برای نسخه نمایشی: دیتابیس SQLite با داده نمونه می‌سازد.
declare(strict_types=1);

require __DIR__ . '/../parseh_app/bootstrap.php';

$sql = file_get_contents(__DIR__ . '/../database/schema.sql') . "\n" . file_get_contents(__DIR__ . '/../database/seed.sql');
$sql = preg_replace('/^SET [^;]+;/m', '', $sql);
$sql = preg_replace('/(BIG)?INT UNSIGNED AUTO_INCREMENT PRIMARY KEY/', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
$sql = str_replace('UNSIGNED', '', $sql);
$sql = preg_replace('/\)\s*ENGINE=[^;]*;/', ');', $sql);
$sql = str_replace('\n', "\n", $sql);
db()->exec($sql);

function national_code(int $seed): string
{
    $base = str_pad((string) (1000000 + $seed * 7919 % 8999999), 9, '0', STR_PAD_LEFT);
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += (int) $base[$i] * (10 - $i);
    }
    $r = $sum % 11;
    return $base . ($r < 2 ? $r : 11 - $r);
}

$t = now();
foreach ([
    ['دکتر سید محمد جواد هاشمی', '09170000001', 1, 'Parseh1405demo'],
    ['مدیر سایت (نمونه)', '09170000003', 2, 'Manager1405demo'],
    ['خانم رضایی — پذیرش', '09170000002', 3, 'Reception1405demo'],
] as [$name, $mobile, $role, $pw]) {
    db_insert('users', ['role_id' => $role, 'name' => $name, 'mobile' => $mobile, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'is_active' => 1, 'created_at' => $t]);
}

foreach (['phone' => '071-36XXXXXX', 'address' => 'شیراز، [آدرس کلینیک]', 'hours' => 'شنبه تا چهارشنبه، ۱۶ تا ۲۰', 'instagram' => '@parseh_radiology', 'doctor_years' => '۲۰', 'doctor_council_no' => 'XXXXX',
          'doctor_bio' => 'متن نمونه: سوابق تحصیلی، دانشگاه محل تحصیل و حوزه‌های تخصصی دکتر هاشمی از پنل مدیریت → تنظیمات سایت قابل ویرایش است.'] as $k => $v) {
    db_query('UPDATE settings SET `value` = ? WHERE `key` = ?', [$v, $k]);
}

foreach (['تأمین اجتماعی', 'سلامت', 'نیروهای مسلح', 'بیمه تکمیلی (نمونه)'] as $i => $name) {
    db_insert('insurances', ['name' => $name, 'is_active' => 1, 'sort_order' => $i]);
}

// برنامه کاری صبح هم اضافه شود تا نوبت خالی بیشتری دیده شود
foreach ([0, 1, 2, 3, 4] as $d) {
    db_insert('schedules', ['service_id' => null, 'weekday' => $d, 'start_time' => '09:00:00', 'end_time' => '12:00:00', 'capacity' => 1]);
}
db_insert('schedule_exceptions', ['day' => date('Y-m-d', strtotime('+9 day')), 'is_closed' => 1, 'note' => 'تعطیل رسمی (نمونه)']);

$patients = [
    ['مریم', 'احمدی', '09121112233', 'f'], ['سارا', 'کریمی', '09171234567', 'f'], ['علی', 'محمدی', '09179876543', 'm'],
    ['زهرا', 'حسینی', '09363334455', 'f'], ['رضا', 'جعفری', '09175556677', 'm'], ['نرگس', 'موسوی', '09018889900', 'f'],
];
$pids = [];
foreach ($patients as $i => [$f, $l, $m, $g]) {
    $pids[] = db_insert('patients', ['first_name' => $f, 'last_name' => $l, 'mobile' => $m, 'national_code' => national_code($i + 11), 'gender' => $g, 'insurance_id' => ($i % 3) + 1, 'birth_date' => '19' . (70 + $i * 3) . '-05-12', 'created_at' => $t]);
}

$today = date('Y-m-d');
$appts = [
    [0, 1, "-3 day 16:20", 'done', 'online'],
    [1, 2, "-1 day 17:00", 'done', 'phone'],
    [2, 9, "{$today} 16:00", 'checked_in', 'walk_in'],
    [3, 5, "{$today} 16:40", 'confirmed', 'online'],
    [4, 11, "{$today} 17:20", 'confirmed', 'phone'],
    [5, 7, "{$today} 18:00", 'confirmed', 'online'],
    [0, 3, "+1 day 16:20", 'confirmed', 'online'],
    [3, 10, "+2 day 09:30", 'confirmed', 'online'],
    [1, 13, "-2 day 10:00", 'no_show', 'online'],
];
$aids = [];
foreach ($appts as [$pi, $sid, $when, $status, $src]) {
    $at = date('Y-m-d H:i:00', strtotime($when));
    $aids[] = db_insert('appointments', ['patient_id' => $pids[$pi], 'service_id' => $sid, 'starts_at' => $at, 'status' => $status, 'tracking_code' => new_tracking_code(), 'insurance_id' => null, 'source' => $src, 'created_by' => $src === 'online' ? null : 3, 'created_at' => $t]);
}

// یک جواب منتشرشده با PDF و تصویر نمونه
function sample_pdf(): string
{
    $text = 'Parseh Radiology - SAMPLE REPORT (demo only, not a real patient)';
    $objs = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
        null,
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
    ];
    $stream = "BT /F1 16 Tf 60 780 Td ({$text}) Tj ET";
    $objs[3] = '<< /Length ' . strlen($stream) . " >>\nstream\n{$stream}\nendstream";
    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objs as $i => $o) {
        $offsets[] = strlen($pdf);
        $pdf .= ($i + 1) . " 0 obj\n{$o}\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
    foreach ($offsets as $off) {
        $pdf .= sprintf("%010d 00000 n \n", $off);
    }
    return $pdf . "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
}
$dir = STORAGE_PATH . '/private/results/demo';
@mkdir($dir, 0755, true);
file_put_contents($dir . '/report.pdf', sample_pdf());
copy(__DIR__ . '/sample-xray.jpg', $dir . '/xray.jpg');
$rid = db_insert('results', ['appointment_id' => $aids[0], 'report_path' => 'results/demo/report.pdf', 'status' => 'ready', 'uploaded_by' => 1, 'ready_at' => $t, 'expires_at' => date('Y-m-d H:i:s', strtotime('+90 days')), 'created_at' => $t]);
db_insert('result_images', ['result_id' => $rid, 'file_path' => 'results/demo/xray.jpg', 'caption' => 'تصویر نمونه', 'sort_order' => 0]);

db_insert('posts', ['title' => 'آمادگی‌های لازم پیش از سونوگرافی', 'slug' => 'ultrasound-prep', 'body' => "این یک مقاله نمونه است و از پنل مدیریت → مقالات قابل ویرایش است.\n\nبرای سونوگرافی شکم ۶ تا ۸ ساعت ناشتا باشید. برای سونوگرافی لگن یک ساعت قبل چهار تا پنج لیوان آب بنوشید.", 'published_at' => $t, 'created_at' => $t]);
db_insert('contact_messages', ['name' => 'بیمار نمونه', 'mobile' => '09120000000', 'message' => 'سلام، آیا سونوگرافی داپلر روز پنجشنبه هم انجام می‌شود؟', 'is_read' => 0, 'created_at' => $t]);

echo "demo database ready\n";
echo "tracking code of sample result: " . db_value('SELECT tracking_code FROM appointments WHERE id = ?', [$aids[0]]) . ' / national code ' . national_code(11) . "\n";
