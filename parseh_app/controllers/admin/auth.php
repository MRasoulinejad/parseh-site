<?php
declare(strict_types=1);

function valid_password(string $pw): bool
{
    return mb_strlen($pw) >= 10 && preg_match('/\d/', $pw) && preg_match('/\p{L}/u', $pw);
}

/** راه‌اندازی اولیه: فقط وقتی هیچ کاربری وجود ندارد */
function admin_setup_form(): string
{
    if ((int) db_value('SELECT COUNT(*) FROM users') > 0) {
        redirect('/admin/login');
    }
    return view('admin/setup', ['title' => 'راه‌اندازی اولیه'], 'admin_auth');
}

function admin_setup(): never
{
    if ((int) db_value('SELECT COUNT(*) FROM users') > 0) {
        redirect('/admin/login');
    }
    $name = (string) input('name', '');
    $mobile = (string) input('mobile', '');
    $pw = (string) ($_POST['password'] ?? '');
    if (mb_strlen($name) < 3 || !valid_mobile($mobile) || !valid_password($pw) || $pw !== ($_POST['password_confirm'] ?? '')) {
        flash('error', 'نام، موبایل و رمز (حداقل ۱۰ کاراکتر شامل حرف و عدد، و تکرار یکسان) را درست وارد کنید.');
        redirect('/admin/setup');
    }
    $roleId = (int) db_value('SELECT id FROM roles WHERE is_full_access = 1 ORDER BY id LIMIT 1');
    $id = db_insert('users', [
        'role_id'       => $roleId,
        'name'          => $name,
        'mobile'        => $mobile,
        'password_hash' => password_hash($pw, PASSWORD_DEFAULT),
        'is_active'     => 1,
        'created_at'    => now(),
    ]);
    staff_login(db_find('users', $id));
    audit('setup', 'users', $id);
    flash('success', 'حساب دسترسی کامل ساخته شد. حالا تنظیمات سایت و برنامه کاری را کامل کنید.');
    redirect('/admin/settings');
}

function admin_login_form(): string
{
    if ((int) db_value('SELECT COUNT(*) FROM users') === 0) {
        redirect('/admin/setup');
    }
    if (staff()) {
        redirect('/admin');
    }
    return view('admin/login', ['title' => 'ورود پرسنل'], 'admin_auth');
}

function admin_login(): never
{
    $mobile = (string) input('mobile', '');
    $pw = (string) ($_POST['password'] ?? '');
    if (!rate_limit('staff-login:' . $mobile, 5, 900) || !rate_limit('staff-login-ip:' . client_ip(), 20, 900)) {
        flash('error', 'تلاش‌های ناموفق زیاد بود. ۱۵ دقیقه دیگر دوباره امتحان کنید.');
        redirect('/admin/login');
    }
    $user = db_one('SELECT * FROM users WHERE mobile = ? AND is_active = 1', [$mobile]);
    if (!$user || !password_verify($pw, $user['password_hash'])) {
        flash('error', 'شماره موبایل یا رمز عبور اشتباه است.');
        redirect('/admin/login');
    }
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        db_update('users', ['password_hash' => password_hash($pw, PASSWORD_DEFAULT)], 'id = ?', [$user['id']]);
    }
    db_query('DELETE FROM rate_limits WHERE `key` = ?', ['staff-login:' . $mobile]);
    staff_login($user);
    audit('login', 'users', (int) $user['id']);
    $home = staff_can('dashboard.view') ? '/admin' : '/admin/appointments';
    redirect(intended($home));
}

function admin_logout(): never
{
    if (staff()) {
        audit('logout', 'users', (int) staff()['id']);
    }
    unset($_SESSION['staff_id']);
    session_regenerate_id(true);
    redirect('/admin/login');
}

function admin_password_form(): string
{
    require_staff();
    return view('admin/password', ['title' => 'تغییر رمز عبور'], 'admin');
}

function admin_password_save(): never
{
    $me = require_staff();
    $current = (string) ($_POST['current'] ?? '');
    $new = (string) ($_POST['password'] ?? '');
    if (!password_verify($current, $me['password_hash'])) {
        flash('error', 'رمز فعلی اشتباه است.');
        redirect('/admin/password');
    }
    if (!valid_password($new) || $new !== ($_POST['password_confirm'] ?? '')) {
        flash('error', 'رمز جدید باید حداقل ۱۰ کاراکتر و شامل حرف و عدد باشد و تکرار آن یکسان.');
        redirect('/admin/password');
    }
    db_update('users', ['password_hash' => password_hash($new, PASSWORD_DEFAULT)], 'id = ?', [$me['id']]);
    audit('change_password', 'users', (int) $me['id']);
    session_regenerate_id(true);
    flash('success', 'رمز عبور تغییر کرد.');
    redirect('/admin/password');
}
