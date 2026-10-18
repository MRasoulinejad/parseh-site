<?php
declare(strict_types=1);

function admin_dashboard(): string
{
    $me = require_staff();
    if (!staff_can('dashboard.view')) {
        redirect(staff_can('appointments.view') ? '/admin/appointments' : '/admin/password');
    }
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $stats = [
        'today'     => (int) db_value("SELECT COUNT(*) FROM appointments WHERE starts_at >= ? AND starts_at < ? AND status <> 'cancelled'", [$today . ' 00:00:00', $tomorrow . ' 00:00:00']),
        'waiting'   => (int) db_value("SELECT COUNT(*) FROM appointments WHERE starts_at >= ? AND starts_at < ? AND status IN ('pending','confirmed')", [$today . ' 00:00:00', $tomorrow . ' 00:00:00']),
        'results'   => (int) db_value("SELECT COUNT(*) FROM appointments a LEFT JOIN results r ON r.appointment_id = a.id WHERE a.status = 'done' AND (r.id IS NULL OR r.status = 'draft')"),
        'week'      => (int) db_value("SELECT COUNT(*) FROM appointments WHERE starts_at >= ? AND starts_at < ? AND status <> 'cancelled'", [$today . ' 00:00:00', date('Y-m-d', strtotime('+7 day')) . ' 00:00:00']),
    ];
    $todayList = db_all(
        "SELECT a.*, p.first_name, p.last_name, p.mobile, s.title AS service_title FROM appointments a
         JOIN patients p ON p.id = a.patient_id JOIN services s ON s.id = a.service_id
         WHERE a.starts_at >= ? AND a.starts_at < ? AND a.status <> 'cancelled' ORDER BY a.starts_at",
        [$today . ' 00:00:00', $tomorrow . ' 00:00:00']
    );
    return view('admin/dashboard', ['stats' => $stats, 'todayList' => $todayList, 'title' => 'داشبورد — ' . jdate($today, 'long')], 'admin');
}
