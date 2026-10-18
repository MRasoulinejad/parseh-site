<?php
declare(strict_types=1);

function admin_patients(): string
{
    require_staff('patients.view');
    $q = (string) input('q', '');
    if ($q !== '') {
        $page = paginate('SELECT * FROM patients WHERE first_name LIKE ? OR last_name LIKE ? OR mobile LIKE ? OR national_code = ? ORDER BY id DESC', ["%{$q}%", "%{$q}%", "%{$q}%", $q]);
    } else {
        $page = paginate('SELECT * FROM patients ORDER BY id DESC');
    }
    return view('admin/patients', ['page' => $page, 'q' => $q, 'title' => 'بیماران'], 'admin');
}

function admin_patient_show(string $id): string
{
    require_staff('patients.view');
    $p = db_one('SELECT p.*, i.name AS insurance_name FROM patients p LEFT JOIN insurances i ON i.id = p.insurance_id WHERE p.id = ?', [(int) $id]);
    if (!$p) {
        abort(404);
    }
    $appointments = db_all(
        'SELECT a.*, s.title AS service_title, ? AS first_name, ? AS last_name, ? AS mobile FROM appointments a JOIN services s ON s.id = a.service_id WHERE a.patient_id = ? ORDER BY a.starts_at DESC',
        [$p['first_name'], $p['last_name'], $p['mobile'], $p['id']]
    );
    audit('view', 'patients', (int) $p['id']);
    return view('admin/patient_show', ['p' => $p, 'appointments' => $appointments, 'title' => $p['first_name'] . ' ' . $p['last_name']], 'admin');
}

function admin_patient_form(?int $id): string
{
    require_staff('patients.manage');
    $p = $id ? db_find('patients', $id) : null;
    if ($id && !$p) {
        abort(404);
    }
    return view('admin/patient_form', [
        'p'          => $p,
        'insurances' => db_all('SELECT id, name FROM insurances WHERE is_active = 1 ORDER BY sort_order'),
        'title'      => $p ? 'ویرایش بیمار' : 'بیمار جدید',
    ], 'admin');
}

function admin_patient_save(?int $id): never
{
    require_staff('patients.manage');
    $back = $id ? "/admin/patients/{$id}/edit" : '/admin/patients/new' . (input('back') ? '?back=' . rawurlencode((string) input('back')) : '');
    $data = patient_validate_input(false);
    $mobile = (string) input('mobile', '');
    if (!valid_mobile($mobile)) {
        with_old($_POST);
        flash('error', 'شماره موبایل معتبر نیست.');
        redirect($back);
    }
    if ($data === null) {
        redirect($back);
    }
    if ($data['national_code'] && db_value('SELECT 1 FROM patients WHERE national_code = ? AND id <> ?', [$data['national_code'], $id ?? 0])) {
        with_old($_POST);
        flash('error', 'بیمار دیگری با این کد ملی ثبت شده است.');
        redirect($back);
    }
    $data['mobile'] = $mobile;
    $data['notes'] = mb_substr((string) input('notes', ''), 0, 500) ?: null;
    if ($id) {
        db_update('patients', $data, 'id = ?', [$id]);
        audit('update', 'patients', $id);
    } else {
        $data['created_at'] = now();
        $id = db_insert('patients', $data);
        audit('create', 'patients', $id);
    }
    flash('success', 'اطلاعات بیمار ذخیره شد.');
    if (input('back') === 'appointment' && staff_can('appointments.manage')) {
        redirect('/admin/appointments/new?patient_id=' . $id);
    }
    redirect('/admin/patients/' . $id);
}
