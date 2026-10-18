<?php
declare(strict_types=1);

const APPOINTMENT_STATUSES = ['pending', 'confirmed', 'checked_in', 'done', 'cancelled', 'no_show'];

function admin_appointments(): string
{
    require_staff('appointments.view');
    $q = (string) input('q', '');
    $status = (string) input('status', '');
    $dateInput = (string) input('date', '');
    $date = jalali_input_to_date($dateInput) ?? date('Y-m-d');
    $where = [];
    $params = [];
    if ($q !== '') {
        $where[] = '(p.first_name LIKE ? OR p.last_name LIKE ? OR p.mobile LIKE ? OR p.national_code = ? OR a.tracking_code = ?)';
        array_push($params, "%{$q}%", "%{$q}%", "%{$q}%", $q, strtoupper($q));
    } else {
        $where[] = 'a.starts_at >= ? AND a.starts_at < ?';
        array_push($params, $date . ' 00:00:00', date('Y-m-d', strtotime($date . ' +1 day')) . ' 00:00:00');
    }
    if (in_array($status, APPOINTMENT_STATUSES, true)) {
        $where[] = 'a.status = ?';
        $params[] = $status;
    }
    $sql = 'SELECT a.*, p.first_name, p.last_name, p.mobile, s.title AS service_title FROM appointments a
            JOIN patients p ON p.id = a.patient_id JOIN services s ON s.id = a.service_id
            WHERE ' . implode(' AND ', $where) . ' ORDER BY a.starts_at ' . ($q !== '' ? 'DESC' : 'ASC');
    $page = paginate($sql, $params, 50);
    return view('admin/appointments', [
        'page'   => $page,
        'q'      => $q,
        'status' => $status,
        'date'   => $date,
        'title'  => $q !== '' ? 'جستجوی نوبت‌ها' : 'نوبت‌های ' . jdate($date, 'long'),
    ], 'admin');
}

function admin_appointment_form(): string
{
    require_staff('appointments.manage');
    $q = (string) input('q', '');
    $matches = [];
    if ($q !== '') {
        $matches = db_all('SELECT * FROM patients WHERE national_code = ? OR mobile = ? OR last_name LIKE ? ORDER BY id DESC LIMIT 20', [$q, $q, "%{$q}%"]);
    }
    $selected = input_int('patient_id') ? db_find('patients', input_int('patient_id')) : null;
    return view('admin/appointment_form', [
        'q'          => $q,
        'matches'    => $matches,
        'selected'   => $selected,
        'services'   => db_all('SELECT id, title FROM services WHERE is_active = 1 ORDER BY sort_order'),
        'insurances' => db_all('SELECT id, name FROM insurances WHERE is_active = 1 ORDER BY sort_order'),
        'title'      => 'ثبت نوبت',
    ], 'admin');
}

function admin_appointment_store(): never
{
    $me = require_staff('appointments.manage');
    $patient = db_find('patients', input_int('patient_id') ?? 0);
    $service = db_find('services', input_int('service_id') ?? 0);
    $date = jalali_input_to_date((string) input('date', ''));
    $time = (string) input('time', '');
    $source = in_array(input('source'), ['phone', 'walk_in'], true) ? input('source') : 'phone';
    if (!$patient || !$service || !$date || !preg_match('/^\d{2}:\d{2}$/', $time)) {
        flash('error', 'بیمار، خدمت، تاریخ (مثل ۱۴۰۵/۰۸/۰۱) و ساعت را کامل وارد کنید.');
        redirect('/admin/appointments/new' . ($patient ? '?patient_id=' . $patient['id'] : ''));
    }
    $startsAt = $date . ' ' . $time . ':00';
    $code = new_tracking_code();
    $id = db_insert('appointments', [
        'patient_id'    => $patient['id'],
        'service_id'    => $service['id'],
        'starts_at'     => $startsAt,
        'status'        => $source === 'walk_in' ? 'checked_in' : 'confirmed',
        'tracking_code' => $code,
        'insurance_id'  => input_int('insurance_id') ?: $patient['insurance_id'],
        'source'        => $source,
        'notes'         => mb_substr((string) input('notes', ''), 0, 500) ?: null,
        'created_by'    => $me['id'],
        'created_at'    => now(),
    ]);
    audit('create', 'appointments', $id);
    if (input('send_sms')) {
        sms_send($patient['mobile'], 'confirm',
            "نوبت شما در رادیولوژی پارسه ثبت شد.\n{$service['title']}\n" . jdate($startsAt, 'long') . ' ساعت ' . fa_num($time) . "\nکد پیگیری: {$code}");
    }
    flash('success', 'نوبت ثبت شد. کد پیگیری: ' . $code);
    redirect('/admin/appointments/' . $id);
}

function admin_appointment_show(string $id): string
{
    require_staff('appointments.view');
    $a = db_one(
        'SELECT a.*, p.first_name, p.last_name, p.mobile, p.national_code, s.title AS service_title, s.prep_text,
                i.name AS insurance_name, u.name AS creator_name
         FROM appointments a JOIN patients p ON p.id = a.patient_id JOIN services s ON s.id = a.service_id
         LEFT JOIN insurances i ON i.id = a.insurance_id LEFT JOIN users u ON u.id = a.created_by WHERE a.id = ?',
        [(int) $id]
    );
    if (!$a) {
        abort(404);
    }
    $result = db_one('SELECT * FROM results WHERE appointment_id = ?', [$a['id']]);
    $images = $result ? db_all('SELECT * FROM result_images WHERE result_id = ? ORDER BY sort_order, id', [$result['id']]) : [];
    audit('view', 'appointments', (int) $a['id']);
    return view('admin/appointment_show', ['a' => $a, 'result' => $result, 'images' => $images, 'title' => 'نوبت ' . $a['tracking_code']], 'admin');
}

function admin_appointment_status(string $id): never
{
    require_staff('appointments.manage');
    $status = (string) input('status', '');
    $a = db_find('appointments', (int) $id);
    if (!$a || !in_array($status, APPOINTMENT_STATUSES, true)) {
        abort(400);
    }
    db_update('appointments', ['status' => $status], 'id = ?', [$a['id']]);
    audit('status_' . $status, 'appointments', (int) $a['id']);
    flash('success', 'وضعیت به «' . status_label($status) . '» تغییر کرد.');
    redirect('/admin/appointments/' . $a['id']);
}

function admin_appointment_notes(string $id): never
{
    require_staff('appointments.manage');
    db_update('appointments', ['notes' => mb_substr((string) input('notes', ''), 0, 500) ?: null], 'id = ?', [(int) $id]);
    flash('success', 'یادداشت ذخیره شد.');
    redirect('/admin/appointments/' . (int) $id);
}
