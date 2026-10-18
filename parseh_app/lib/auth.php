<?php
declare(strict_types=1);

/** فهرست همه مجوزهای قابل تعریف برای گروه‌های کاربری */
function all_permissions(): array
{
    return [
        'dashboard.view'      => 'دیدن داشبورد',
        'appointments.view'   => 'دیدن نوبت‌ها',
        'appointments.manage' => 'ثبت و ویرایش نوبت‌ها',
        'patients.view'       => 'دیدن بیماران',
        'patients.manage'     => 'ثبت و ویرایش بیماران',
        'results.manage'      => 'بارگذاری و انتشار جواب‌ها',
        'services.manage'     => 'خدمات و برنامه کاری',
        'content.manage'      => 'محتوای سایت (گالری، مقالات، بیمه‌ها، تنظیمات)',
        'messages.reply'      => 'دیدن و پاسخ به پیام‌های فرم تماس',
        'users.manage'        => 'تعریف پرسنل و گروه‌های کاربری',
        'logs.view'           => 'دیدن گزارش فعالیت‌ها و پیامک‌ها',
    ];
}

/* ---------- پرسنل ---------- */

function staff(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $user = null;
    if (!empty($_SESSION['staff_id'])) {
        $row = db_one(
            'SELECT u.*, r.name AS role_name, r.permissions AS role_permissions, r.is_full_access
             FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND u.is_active = 1',
            [(int) $_SESSION['staff_id']]
        );
        if ($row) {
            $row['perms'] = json_decode((string) $row['role_permissions'], true) ?: [];
            $user = $row;
        } else {
            unset($_SESSION['staff_id']);
        }
    }
    return $user;
}

function is_full_access(?array $user = null): bool
{
    $user ??= staff();
    return $user !== null && (int) $user['is_full_access'] === 1;
}

function staff_can(string $perm): bool
{
    $user = staff();
    if (!$user) {
        return false;
    }
    return is_full_access($user) || in_array($perm, $user['perms'], true);
}

function require_staff(?string $perm = null): array
{
    $user = staff();
    if (!$user) {
        $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '/admin';
        redirect('/admin/login');
    }
    if ($perm !== null && !staff_can($perm)) {
        abort(403, 'گروه کاربری شما به این بخش دسترسی ندارد.');
    }
    return $user;
}

function staff_login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['staff_id'] = (int) $user['id'];
    db_update('users', ['last_login_at' => now()], 'id = ?', [$user['id']]);
}

/* ---------- بیمار ---------- */

function patient(): ?array
{
    static $p = false;
    if ($p !== false) {
        return $p;
    }
    $p = null;
    if (!empty($_SESSION['patient_id'])) {
        $p = db_find('patients', (int) $_SESSION['patient_id']);
    }
    return $p;
}

function require_patient(): array
{
    $p = patient();
    if (!$p) {
        $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '/panel';
        redirect('/login');
    }
    return $p;
}

function intended(string $default): string
{
    $to = $_SESSION['_intended'] ?? $default;
    unset($_SESSION['_intended']);
    return str_starts_with($to, '/') ? $to : $default;
}

/* ---------- کد یک‌بارمصرف ---------- */

/** کد یک‌بارمصرف می‌سازد و پیامک می‌کند؛ در صورت محدودیت null برمی‌گرداند */
function otp_issue(string $mobile): ?string
{
    if (!rate_limit('otp:' . $mobile, 3, 600) || !rate_limit('otp-ip:' . client_ip(), 10, 3600)) {
        return null;
    }
    $code = (string) random_int(10000, 99999);
    db_insert('otp_codes', [
        'mobile'     => $mobile,
        'code_hash'  => password_hash($code, PASSWORD_DEFAULT),
        'attempts'   => 0,
        'expires_at' => date('Y-m-d H:i:s', time() + 120),
    ]);
    sms_send($mobile, 'otp', 'کد ورود شما به سایت ' . config('app_name') . ': ' . $code, ['CODE' => $code]);
    return $code;
}

function otp_verify(string $mobile, string $code): bool
{
    $row = db_one(
        'SELECT * FROM otp_codes WHERE mobile = ? AND used_at IS NULL AND expires_at > ? ORDER BY id DESC LIMIT 1',
        [$mobile, now()]
    );
    if (!$row || (int) $row['attempts'] >= 5) {
        return false;
    }
    if (!password_verify($code, $row['code_hash'])) {
        db_query('UPDATE otp_codes SET attempts = attempts + 1 WHERE id = ?', [$row['id']]);
        return false;
    }
    db_update('otp_codes', ['used_at' => now()], 'id = ?', [$row['id']]);
    return true;
}
