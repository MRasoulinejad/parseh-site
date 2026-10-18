<?php
declare(strict_types=1);

function admin_results(): string
{
    require_staff('results.manage');
    $pending = db_all(
        "SELECT a.*, p.first_name, p.last_name, p.mobile, s.title AS service_title, r.status AS result_status
         FROM appointments a JOIN patients p ON p.id = a.patient_id JOIN services s ON s.id = a.service_id
         LEFT JOIN results r ON r.appointment_id = a.id
         WHERE a.status IN ('done','checked_in') AND (r.id IS NULL OR r.status = 'draft')
         ORDER BY a.starts_at DESC LIMIT 200"
    );
    $recent = db_all(
        "SELECT r.*, a.tracking_code, a.starts_at, p.first_name, p.last_name, s.title AS service_title
         FROM results r JOIN appointments a ON a.id = r.appointment_id JOIN patients p ON p.id = a.patient_id
         JOIN services s ON s.id = a.service_id WHERE r.status = 'ready' ORDER BY r.ready_at DESC LIMIT 30"
    );
    return view('admin/results', ['pending' => $pending, 'recent' => $recent, 'title' => 'جواب‌ها'], 'admin');
}

function admin_result_upload(string $appointmentId): never
{
    $me = require_staff('results.manage');
    $a = db_find('appointments', (int) $appointmentId);
    if (!$a) {
        abort(404);
    }
    $result = db_one('SELECT * FROM results WHERE appointment_id = ?', [$a['id']]);
    if (!$result) {
        $rid = db_insert('results', ['appointment_id' => $a['id'], 'status' => 'draft', 'uploaded_by' => $me['id'], 'created_at' => now()]);
        $result = db_find('results', $rid);
    }
    $changed = false;
    if (!empty($_FILES['report']) && $_FILES['report']['error'] !== UPLOAD_ERR_NO_FILE) {
        $path = store_upload($_FILES['report'], 'results', ['pdf'], true, 15);
        if ($path) {
            delete_private($result['report_path']);
            db_update('results', ['report_path' => $path, 'uploaded_by' => $me['id']], 'id = ?', [$result['id']]);
            $changed = true;
        }
    }
    $existing = (int) db_value('SELECT COUNT(*) FROM result_images WHERE result_id = ?', [$result['id']]);
    foreach (normalize_files($_FILES['images'] ?? null) as $i => $file) {
        if ($existing >= 4) {
            flash('error', 'حداکثر ۴ تصویر برای هر جواب مجاز است.');
            break;
        }
        $path = store_upload($file, 'results', ['jpg', 'png', 'webp'], true, 8);
        if ($path) {
            db_insert('result_images', ['result_id' => $result['id'], 'file_path' => $path, 'sort_order' => $existing]);
            $existing++;
            $changed = true;
        }
    }
    if ($changed) {
        audit('upload_result', 'results', (int) $result['id']);
        if ($a['status'] !== 'done') {
            db_update('appointments', ['status' => 'done'], 'id = ?', [$a['id']]);
        }
        flash('success', 'فایل‌ها بارگذاری شد. برای دسترسی بیمار، «انتشار برای بیمار» را بزنید.');
    }
    redirect('/admin/appointments/' . $a['id']);
}

function admin_result_publish(string $id): never
{
    require_staff('results.manage');
    $r = db_find('results', (int) $id);
    if (!$r) {
        abort(404);
    }
    $days = (int) config('result_retention_days', 90);
    db_update('results', [
        'status'     => 'ready',
        'ready_at'   => now(),
        'expires_at' => date('Y-m-d H:i:s', strtotime("+{$days} days")),
    ], 'id = ?', [$r['id']]);
    audit('publish_result', 'results', (int) $r['id']);
    $a = db_one('SELECT a.tracking_code, p.mobile FROM appointments a JOIN patients p ON p.id = a.patient_id WHERE a.id = ?', [$r['appointment_id']]);
    if (input('send_sms') && $a) {
        $site = rtrim((string) config('app_url'), '/');
        sms_send($a['mobile'], 'result_ready', "جواب تصویربرداری شما در رادیولوژی پارسه آماده است.\nدریافت: {$site}/results\nکد پیگیری: {$a['tracking_code']}", ['CODE' => $a['tracking_code']]);
    }
    flash('success', 'جواب منتشر شد و تا ' . fa_num((string) $days) . ' روز در دسترس بیمار است.');
    redirect('/admin/appointments/' . $r['appointment_id']);
}

function admin_result_unpublish(string $id): never
{
    require_staff('results.manage');
    $r = db_find('results', (int) $id);
    if (!$r) {
        abort(404);
    }
    db_update('results', ['status' => 'draft'], 'id = ?', [$r['id']]);
    audit('unpublish_result', 'results', (int) $r['id']);
    flash('success', 'جواب از دسترس بیمار خارج شد.');
    redirect('/admin/appointments/' . $r['appointment_id']);
}

function admin_result_report_delete(string $id): never
{
    require_staff('results.manage');
    $r = db_find('results', (int) $id);
    if (!$r) {
        abort(404);
    }
    delete_private($r['report_path']);
    db_update('results', ['report_path' => null], 'id = ?', [$r['id']]);
    audit('delete_report', 'results', (int) $r['id']);
    redirect('/admin/appointments/' . $r['appointment_id']);
}

function admin_result_image_delete(string $id): never
{
    require_staff('results.manage');
    $img = db_one('SELECT ri.*, r.appointment_id FROM result_images ri JOIN results r ON r.id = ri.result_id WHERE ri.id = ?', [(int) $id]);
    if (!$img) {
        abort(404);
    }
    delete_private($img['file_path']);
    db_query('DELETE FROM result_images WHERE id = ?', [$img['id']]);
    audit('delete_image', 'results', (int) $img['result_id']);
    redirect('/admin/appointments/' . $img['appointment_id']);
}
