<?php
declare(strict_types=1);

/* ---------- ورود با پیامک ---------- */

function patient_login_form(): string
{
    $next = (string) ($_GET['next'] ?? '');
    if (str_starts_with($next, '/booking') || str_starts_with($next, '/panel')) {
        $_SESSION['_intended'] = $next;
    }
    if (patient()) {
        redirect(intended('/panel'));
    }
    return view('patient/login', ['title' => 'ورود بیماران'], 'site');
}

function patient_login_send(): never
{
    $mobile = (string) input('mobile', '');
    if (!valid_mobile($mobile)) {
        flash('error', 'شماره موبایل را به شکل ۰۹۱۲۳۴۵۶۷۸۹ وارد کنید.');
        redirect('/login');
    }
    $code = otp_issue($mobile);
    if ($code === null) {
        flash('error', 'تعداد درخواست کد زیاد است. چند دقیقه بعد دوباره تلاش کنید.');
        redirect('/login');
    }
    $_SESSION['otp_mobile'] = $mobile;
    flash('success', 'کد ورود به شماره ' . fa_num($mobile) . ' پیامک شد.');
    if (config('demo')) {
        // فقط نسخه نمایشی: پیامک واقعی ارسال نمی‌شود
        flash('info', 'نسخه نمایشی — کد ورود شما: ' . $code);
    }
    redirect('/login/verify');
}

function patient_verify_form(): string
{
    if (empty($_SESSION['otp_mobile'])) {
        redirect('/login');
    }
    return view('patient/verify', ['mobile' => $_SESSION['otp_mobile'], 'title' => 'کد ورود'], 'site');
}

function patient_verify(): never
{
    $mobile = $_SESSION['otp_mobile'] ?? '';
    $code = (string) input('code', '');
    if (!$mobile) {
        redirect('/login');
    }
    if (!otp_verify($mobile, $code)) {
        flash('error', 'کد اشتباه است یا منقضی شده.');
        redirect('/login/verify');
    }
    unset($_SESSION['otp_mobile']);
    $p = db_one('SELECT * FROM patients WHERE mobile = ? ORDER BY id LIMIT 1', [$mobile]);
    session_regenerate_id(true);
    if (!$p) {
        $_SESSION['verified_mobile'] = $mobile;
        redirect('/profile/new');
    }
    $_SESSION['patient_id'] = (int) $p['id'];
    redirect(intended('/panel'));
}

function patient_profile_form(): string
{
    if (empty($_SESSION['verified_mobile'])) {
        redirect('/login');
    }
    return view('patient/profile', [
        'insurances' => db_all('SELECT id, name FROM insurances WHERE is_active = 1 ORDER BY sort_order, name'),
        'title'      => 'تکمیل مشخصات',
    ], 'site');
}

function patient_profile_save(): never
{
    $mobile = $_SESSION['verified_mobile'] ?? '';
    if (!$mobile) {
        redirect('/login');
    }
    $data = patient_validate_input();
    if ($data === null) {
        redirect('/profile/new');
    }
    if (db_value('SELECT 1 FROM patients WHERE national_code = ?', [$data['national_code']])) {
        with_old($_POST);
        flash('error', 'این کد ملی قبلاً با شماره دیگری ثبت شده است. برای اصلاح با پذیرش کلینیک تماس بگیرید.');
        redirect('/profile/new');
    }
    $data['mobile'] = $mobile;
    $data['created_at'] = now();
    $id = db_insert('patients', $data);
    unset($_SESSION['verified_mobile']);
    $_SESSION['patient_id'] = $id;
    flash('success', 'مشخصات شما ثبت شد.');
    redirect(intended('/panel'));
}

/** اعتبارسنجی مشترک فرم بیمار (سایت و پنل مدیریت) */
function patient_validate_input(bool $nationalRequired = true): ?array
{
    $first = (string) input('first_name', '');
    $last = (string) input('last_name', '');
    $nc = (string) input('national_code', '');
    $birth = jalali_input_to_date((string) input('birth_date', ''));
    $gender = in_array(input('gender'), ['f', 'm'], true) ? input('gender') : null;
    $ins = input_int('insurance_id');
    $errors = [];
    if (mb_strlen($first) < 2 || mb_strlen($last) < 2) {
        $errors[] = 'نام و نام خانوادگی را کامل وارد کنید.';
    }
    if (($nationalRequired || $nc !== '') && !valid_national_code($nc)) {
        $errors[] = 'کد ملی معتبر نیست.';
    }
    if (input('birth_date') && !$birth) {
        $errors[] = 'تاریخ تولد را به شکل ۱۳۷۰/۰۵/۲۱ وارد کنید.';
    }
    if ($ins && !db_value('SELECT 1 FROM insurances WHERE id = ?', [$ins])) {
        $ins = null;
    }
    if ($errors) {
        with_old($_POST);
        foreach ($errors as $err) {
            flash('error', $err);
        }
        return null;
    }
    return [
        'first_name'    => mb_substr($first, 0, 60),
        'last_name'     => mb_substr($last, 0, 80),
        'national_code' => $nc !== '' ? $nc : null,
        'birth_date'    => $birth,
        'gender'        => $gender,
        'insurance_id'  => $ins,
    ];
}

function patient_logout(): never
{
    unset($_SESSION['patient_id'], $_SESSION['result_access'], $_SESSION['result_access_last']);
    session_regenerate_id(true);
    redirect('/');
}

/* ---------- نوبت‌دهی ---------- */

function booking_services(): string
{
    $cats = active_categories_with_services();
    return view('patient/booking_services', ['categories' => $cats, 'title' => 'نوبت‌دهی آنلاین'], 'site');
}

function booking_slots(string $slug): string
{
    $service = db_one('SELECT * FROM services WHERE slug = ? AND is_active = 1', [$slug]);
    if (!$service) {
        abort(404);
    }
    if ((int) $service['is_bookable'] !== 1) {
        flash('info', 'این خدمت نیاز به هماهنگی تلفنی دارد.');
        redirect('/services/' . $slug);
    }
    $days = available_days($service);
    $selected = (string) ($_GET['date'] ?? ($days[0]['date'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected)) {
        $selected = $days[0]['date'] ?? '';
    }
    $slots = $selected ? available_slots($service, $selected) : [];
    return view('patient/booking_slots', [
        'service'    => $service,
        'days'       => $days,
        'selected'   => $selected,
        'slots'      => $slots,
        'insurances' => db_all('SELECT id, name FROM insurances WHERE is_active = 1 ORDER BY sort_order, name'),
        'title'      => 'رزرو ' . $service['title'],
    ], 'site');
}

function booking_confirm(): never
{
    $p = require_patient();
    $service = db_one('SELECT * FROM services WHERE id = ? AND is_active = 1 AND is_bookable = 1', [input_int('service_id') ?? 0]);
    $startsAt = (string) input('starts_at', '');
    if (!$service || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $startsAt)) {
        abort(400);
    }
    if (!rate_limit('book:' . $p['id'], 5, 3600)) {
        flash('error', 'تعداد رزرو در یک ساعت بیش از حد مجاز است.');
        redirect('/panel');
    }
    $active = (int) db_value("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND starts_at > ? AND status IN ('pending','confirmed')", [$p['id'], now()]);
    if ($active >= 3) {
        flash('error', 'شما سه نوبت فعال دارید. برای نوبت بیشتر با پذیرش تماس بگیرید.');
        redirect('/panel');
    }
    $ok = false;
    foreach (available_slots($service, substr($startsAt, 0, 10)) as $slot) {
        if ($slot['starts_at'] === $startsAt) {
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        flash('error', 'این ساعت همین الان پر شد. لطفاً ساعت دیگری انتخاب کنید.');
        redirect('/booking/' . $service['slug'] . '?date=' . substr($startsAt, 0, 10));
    }
    $ins = input_int('insurance_id') ?: ($p['insurance_id'] ? (int) $p['insurance_id'] : null);
    $code = new_tracking_code();
    $id = db_insert('appointments', [
        'patient_id'    => $p['id'],
        'service_id'    => $service['id'],
        'starts_at'     => $startsAt,
        'status'        => 'confirmed',
        'tracking_code' => $code,
        'insurance_id'  => $ins,
        'source'        => 'online',
        'created_at'    => now(),
    ]);
    audit('book', 'appointments', $id);
    sms_send($p['mobile'], 'confirm',
        "نوبت شما در رادیولوژی پارسه ثبت شد.\n{$service['title']}\n" . jdate($startsAt, 'long') . ' ساعت ' . fa_num(jdate($startsAt, 'time')) .
        "\nکد پیگیری: {$code}" . ($service['prep_text'] ? "\nآمادگی: {$service['prep_text']}" : ''),
        ['SERVICE' => $service['title'], 'DATE' => jdate($startsAt, 'date'), 'TIME' => fa_num(jdate($startsAt, 'time')), 'CODE' => $code]);
    redirect('/booking/done/' . $id);
}

function booking_done(string $id): string
{
    $p = require_patient();
    $a = db_one('SELECT a.*, s.title AS service_title, s.prep_text FROM appointments a JOIN services s ON s.id = a.service_id WHERE a.id = ? AND a.patient_id = ?', [(int) $id, $p['id']]);
    if (!$a) {
        abort(404);
    }
    return view('patient/booking_done', ['a' => $a, 'title' => 'نوبت ثبت شد'], 'site');
}

/* ---------- پنل بیمار ---------- */

function patient_panel(): string
{
    $p = require_patient();
    $appointments = db_all(
        'SELECT a.*, s.title AS service_title, s.prep_text, r.id AS result_id, r.status AS result_status
         FROM appointments a JOIN services s ON s.id = a.service_id
         LEFT JOIN results r ON r.appointment_id = a.id
         WHERE a.patient_id = ? ORDER BY a.starts_at DESC',
        [$p['id']]
    );
    $results = [];
    foreach ($appointments as $a) {
        if ($a['result_id'] && $a['result_status'] === 'ready') {
            $results[] = result_bundle((int) $a['result_id']);
        }
    }
    $messages = db_all('SELECT * FROM contact_messages WHERE patient_id = ? OR mobile = ? ORDER BY id DESC LIMIT 20', [$p['id'], $p['mobile']]);
    return view('patient/panel', [
        'p'            => $p,
        'messages'     => $messages,
        'appointments' => $appointments,
        'results'      => $results,
        'cancelBefore' => (int) config('cancel_hours_before', 3),
        'title'        => 'پنل من',
    ], 'site');
}

function patient_cancel(string $id): never
{
    $p = require_patient();
    $a = db_one('SELECT * FROM appointments WHERE id = ? AND patient_id = ?', [(int) $id, $p['id']]);
    $limit = time() + 3600 * (int) config('cancel_hours_before', 3);
    if (!$a || !in_array($a['status'], ['pending', 'confirmed'], true) || strtotime($a['starts_at']) < $limit) {
        flash('error', 'این نوبت قابل لغو آنلاین نیست. با پذیرش تماس بگیرید.');
        redirect('/panel');
    }
    db_update('appointments', ['status' => 'cancelled'], 'id = ?', [$a['id']]);
    audit('cancel', 'appointments', (int) $a['id']);
    flash('success', 'نوبت لغو شد.');
    redirect('/panel');
}
