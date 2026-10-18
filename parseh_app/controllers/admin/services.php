<?php
declare(strict_types=1);

function admin_services(): string
{
    require_staff('services.manage');
    return view('admin/services', [
        'categories' => db_all('SELECT c.*, (SELECT COUNT(*) FROM services s WHERE s.category_id = c.id) AS n FROM service_categories c ORDER BY sort_order, id'),
        'services'   => db_all('SELECT s.*, c.title AS category_title FROM services s JOIN service_categories c ON c.id = s.category_id ORDER BY c.sort_order, s.sort_order, s.id'),
        'title'      => 'خدمات و دسته‌ها',
    ], 'admin');
}

function admin_service_form(?int $id): string
{
    require_staff('services.manage');
    $s = $id ? db_find('services', $id) : null;
    if ($id && !$s) {
        abort(404);
    }
    return view('admin/service_form', [
        's'          => $s,
        'categories' => db_all('SELECT * FROM service_categories ORDER BY sort_order'),
        'title'      => $s ? 'ویرایش خدمت' : 'خدمت جدید',
    ], 'admin');
}

function admin_service_save(?int $id): never
{
    require_staff('services.manage');
    $title = (string) input('title', '');
    $slug = slugify((string) (input('slug') ?: $title));
    $cat = input_int('category_id');
    if (mb_strlen($title) < 2 || !$cat || !db_find('service_categories', $cat)) {
        flash('error', 'عنوان و دسته را وارد کنید.');
        redirect($id ? "/admin/services/{$id}/edit" : '/admin/services/new');
    }
    if (db_value('SELECT 1 FROM services WHERE slug = ? AND id <> ?', [$slug, $id ?? 0])) {
        $slug .= '-' . random_code(3, 'abcdefghjkmnpqrstuvwxyz23456789');
    }
    $data = [
        'category_id'  => $cat,
        'title'        => mb_substr($title, 0, 150),
        'slug'         => $slug,
        'summary'      => mb_substr((string) input('summary', ''), 0, 300) ?: null,
        'body'         => (string) ($_POST['body'] ?? '') ?: null,
        'prep_text'    => trim((string) ($_POST['prep_text'] ?? '')) ?: null,
        'duration_min' => max(5, min(240, input_int('duration_min') ?? 15)),
        'is_bookable'  => input('is_bookable') ? 1 : 0,
        'is_active'    => input('is_active') ? 1 : 0,
        'sort_order'   => input_int('sort_order') ?? 0,
    ];
    if ($id) {
        db_update('services', $data, 'id = ?', [$id]);
    } else {
        $id = db_insert('services', $data);
    }
    audit('save', 'services', $id);
    flash('success', 'خدمت ذخیره شد.');
    redirect('/admin/services');
}

function admin_category_save(): never
{
    require_staff('services.manage');
    $id = input_int('id');
    $title = (string) input('title', '');
    if (mb_strlen($title) < 2) {
        flash('error', 'عنوان دسته را وارد کنید.');
        redirect('/admin/services');
    }
    $data = ['title' => $title, 'sort_order' => input_int('sort_order') ?? 0];
    $slug = slugify((string) (input('slug') ?: $title));
    if (db_value('SELECT 1 FROM service_categories WHERE slug = ? AND id <> ?', [$slug, $id ?? 0])) {
        $slug .= '-' . random_code(3, 'abcdefghjkmnpqrstuvwxyz23456789');
    }
    $data['slug'] = $slug;
    if ($id) {
        db_update('service_categories', $data, 'id = ?', [$id]);
    } else {
        db_insert('service_categories', $data);
    }
    flash('success', 'دسته ذخیره شد.');
    redirect('/admin/services');
}

function admin_category_delete(string $id): never
{
    require_staff('services.manage');
    if (db_value('SELECT 1 FROM services WHERE category_id = ?', [(int) $id])) {
        flash('error', 'این دسته خدمت دارد؛ ابتدا خدمات را به دسته دیگری منتقل کنید.');
    } else {
        db_query('DELETE FROM service_categories WHERE id = ?', [(int) $id]);
        flash('success', 'دسته حذف شد.');
    }
    redirect('/admin/services');
}

/* ---------- برنامه کاری ---------- */

function admin_schedules(): string
{
    require_staff('services.manage');
    return view('admin/schedules', [
        'schedules'  => db_all('SELECT sc.*, s.title AS service_title FROM schedules sc LEFT JOIN services s ON s.id = sc.service_id ORDER BY sc.weekday, sc.start_time'),
        'exceptions' => db_all('SELECT * FROM schedule_exceptions WHERE day >= ? ORDER BY day', [date('Y-m-d')]),
        'services'   => db_all('SELECT id, title FROM services WHERE is_active = 1 AND is_bookable = 1 ORDER BY sort_order'),
        'title'      => 'برنامه کاری و تعطیلات',
    ], 'admin');
}

function admin_schedule_add(): never
{
    require_staff('services.manage');
    $days = array_map('intval', (array) ($_POST['weekdays'] ?? []));
    $start = (string) input('start_time', '');
    $end = (string) input('end_time', '');
    $service = input_int('service_id');
    if (!$days || !preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end) || $start >= $end) {
        flash('error', 'روز، ساعت شروع و پایان را درست انتخاب کنید.');
        redirect('/admin/schedules');
    }
    foreach ($days as $d) {
        if ($d < 0 || $d > 6) {
            continue;
        }
        db_insert('schedules', [
            'service_id' => $service ?: null,
            'weekday'    => $d,
            'start_time' => $start . ':00',
            'end_time'   => $end . ':00',
            'capacity'   => max(1, min(20, input_int('capacity') ?? 1)),
        ]);
    }
    audit('add', 'schedules');
    flash('success', 'بازه کاری اضافه شد.');
    redirect('/admin/schedules');
}

function admin_schedule_delete(string $id): never
{
    require_staff('services.manage');
    db_query('DELETE FROM schedules WHERE id = ?', [(int) $id]);
    audit('delete', 'schedules', (int) $id);
    redirect('/admin/schedules');
}

function admin_exception_add(): never
{
    require_staff('services.manage');
    $day = jalali_input_to_date((string) input('day', ''));
    if (!$day) {
        flash('error', 'تاریخ را به شکل ۱۴۰۵/۰۸/۰۱ وارد کنید.');
        redirect('/admin/schedules');
    }
    $closed = input('is_closed') ? 1 : 0;
    $start = (string) input('start_time', '');
    $end = (string) input('end_time', '');
    db_query('DELETE FROM schedule_exceptions WHERE day = ?', [$day]);
    db_insert('schedule_exceptions', [
        'day'        => $day,
        'is_closed'  => $closed,
        'start_time' => !$closed && preg_match('/^\d{2}:\d{2}$/', $start) ? $start . ':00' : null,
        'end_time'   => !$closed && preg_match('/^\d{2}:\d{2}$/', $end) ? $end . ':00' : null,
        'note'       => mb_substr((string) input('note', ''), 0, 200) ?: null,
    ]);
    $booked = (int) db_value("SELECT COUNT(*) FROM appointments WHERE starts_at >= ? AND starts_at < ? AND status IN ('pending','confirmed')", [$day . ' 00:00:00', date('Y-m-d', strtotime($day . ' +1 day')) . ' 00:00:00']);
    flash('success', 'روز استثنا ثبت شد.' . ($booked ? ' توجه: ' . fa_num((string) $booked) . ' نوبت فعال در این روز ثبت شده است؛ با بیماران تماس بگیرید.' : ''));
    redirect('/admin/schedules');
}

function admin_exception_delete(string $id): never
{
    require_staff('services.manage');
    db_query('DELETE FROM schedule_exceptions WHERE id = ?', [(int) $id]);
    redirect('/admin/schedules');
}
