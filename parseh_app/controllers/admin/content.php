<?php
declare(strict_types=1);

/* ---------- بیمه‌ها ---------- */

function admin_insurances(): string
{
    require_staff('content.manage');
    return view('admin/insurances', ['rows' => db_all('SELECT * FROM insurances ORDER BY sort_order, id'), 'title' => 'بیمه‌های طرف قرارداد'], 'admin');
}

function admin_insurance_save(): never
{
    require_staff('content.manage');
    $id = input_int('id');
    $name = (string) input('name', '');
    if (mb_strlen($name) < 2) {
        flash('error', 'نام بیمه را وارد کنید.');
        redirect('/admin/insurances');
    }
    $data = ['name' => $name, 'sort_order' => input_int('sort_order') ?? 0, 'is_active' => input('is_active') ? 1 : 0];
    if (!empty($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($path = store_upload($_FILES['logo'], 'insurances', ['png', 'jpg', 'webp'], false, 2)) {
            $data['logo_path'] = $path;
            if ($id && ($old = db_find('insurances', $id))) {
                delete_public($old['logo_path']);
            }
        }
    }
    if ($id) {
        db_update('insurances', $data, 'id = ?', [$id]);
    } else {
        db_insert('insurances', $data);
    }
    flash('success', 'ذخیره شد.');
    redirect('/admin/insurances');
}

function admin_insurance_delete(string $id): never
{
    require_staff('content.manage');
    $row = db_find('insurances', (int) $id);
    if ($row) {
        delete_public($row['logo_path']);
        db_query('DELETE FROM insurances WHERE id = ?', [$row['id']]);
    }
    redirect('/admin/insurances');
}

/* ---------- گالری ---------- */

function admin_gallery(): string
{
    require_staff('content.manage');
    return view('admin/gallery', [
        'items'    => db_all('SELECT g.*, s.title AS service_title FROM gallery_items g LEFT JOIN services s ON s.id = g.service_id ORDER BY g.service_id, g.sort_order, g.id'),
        'services' => db_all('SELECT id, title FROM services ORDER BY sort_order'),
        'title'    => 'گالری تصاویر خدمات',
    ], 'admin');
}

function admin_gallery_add(): never
{
    require_staff('content.manage');
    $service = input_int('service_id');
    $added = 0;
    foreach (normalize_files($_FILES['images'] ?? null) as $file) {
        if ($path = store_upload($file, 'gallery', ['jpg', 'png', 'webp'], false, 5)) {
            db_insert('gallery_items', ['service_id' => $service ?: null, 'file_path' => $path, 'caption' => mb_substr((string) input('caption', ''), 0, 200) ?: null, 'sort_order' => 0]);
            $added++;
        }
    }
    if ($added) {
        flash('success', fa_num((string) $added) . ' تصویر اضافه شد.');
    }
    redirect('/admin/gallery');
}

function admin_gallery_delete(string $id): never
{
    require_staff('content.manage');
    $row = db_find('gallery_items', (int) $id);
    if ($row) {
        delete_public($row['file_path']);
        db_query('DELETE FROM gallery_items WHERE id = ?', [$row['id']]);
    }
    redirect('/admin/gallery');
}

/* ---------- مقالات ---------- */

function admin_posts(): string
{
    require_staff('content.manage');
    return view('admin/posts', ['rows' => db_all('SELECT id, title, slug, published_at FROM posts ORDER BY id DESC'), 'title' => 'مقالات'], 'admin');
}

function admin_post_form(?int $id): string
{
    require_staff('content.manage');
    $post = $id ? db_find('posts', $id) : null;
    if ($id && !$post) {
        abort(404);
    }
    return view('admin/post_form', ['post' => $post, 'title' => $post ? 'ویرایش مقاله' : 'مقاله جدید'], 'admin');
}

function admin_post_save(?int $id): never
{
    require_staff('content.manage');
    $title = (string) input('title', '');
    $body = (string) ($_POST['body'] ?? '');
    if (mb_strlen($title) < 3 || mb_strlen(trim($body)) < 10) {
        flash('error', 'عنوان و متن مقاله را وارد کنید.');
        redirect($id ? "/admin/posts/{$id}/edit" : '/admin/posts/new');
    }
    $slug = slugify((string) (input('slug') ?: $title));
    if (db_value('SELECT 1 FROM posts WHERE slug = ? AND id <> ?', [$slug, $id ?? 0])) {
        $slug .= '-' . random_code(3, 'abcdefghjkmnpqrstuvwxyz23456789');
    }
    $data = ['title' => $title, 'slug' => $slug, 'body' => $body];
    $existing = $id ? db_find('posts', $id) : null;
    if (input('publish')) {
        $data['published_at'] = $existing['published_at'] ?? now();
    } else {
        $data['published_at'] = null;
    }
    if (!empty($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($path = store_upload($_FILES['cover'], 'posts', ['jpg', 'png', 'webp'], false, 5)) {
            $data['cover_path'] = $path;
            delete_public($existing['cover_path'] ?? null);
        }
    }
    if ($id) {
        db_update('posts', $data, 'id = ?', [$id]);
    } else {
        $data['created_at'] = now();
        $id = db_insert('posts', $data);
    }
    flash('success', 'مقاله ذخیره شد.');
    redirect('/admin/posts');
}

function admin_post_delete(string $id): never
{
    require_staff('content.manage');
    $row = db_find('posts', (int) $id);
    if ($row) {
        delete_public($row['cover_path']);
        db_query('DELETE FROM posts WHERE id = ?', [$row['id']]);
    }
    redirect('/admin/posts');
}

/* ---------- پیام‌ها ---------- */

function admin_messages(): string
{
    require_staff('content.manage');
    return view('admin/messages', ['page' => paginate('SELECT * FROM contact_messages ORDER BY is_read, id DESC'), 'title' => 'پیام‌های فرم تماس'], 'admin');
}

function admin_message_read(string $id): never
{
    require_staff('content.manage');
    db_update('contact_messages', ['is_read' => 1], 'id = ?', [(int) $id]);
    redirect('/admin/messages');
}

function admin_message_delete(string $id): never
{
    require_staff('content.manage');
    db_query('DELETE FROM contact_messages WHERE id = ?', [(int) $id]);
    redirect('/admin/messages');
}

/* ---------- تنظیمات ---------- */

const SETTING_FIELDS = [
    'phone'             => 'تلفن پذیرش',
    'address'           => 'آدرس',
    'hours'             => 'ساعات کاری (متن نمایشی)',
    'instagram'         => 'اینستاگرام (مثلاً @parseh_radiology)',
    'map_url'           => 'لینک embed نقشه (نشان یا گوگل مپ، با https)',
    'hero_title'        => 'تیتر صفحه اول',
    'hero_text'         => 'متن معرفی صفحه اول',
    'doctor_bio'        => 'بیوگرافی دکتر هاشمی',
    'doctor_council_no' => 'شماره نظام پزشکی',
    'doctor_years'      => 'سال‌های سابقه',
];

function admin_settings(): string
{
    require_staff('content.manage');
    $values = [];
    foreach (SETTING_FIELDS as $k => $_) {
        $values[$k] = str_replace('\n', "\n", setting($k));
    }
    return view('admin/settings', ['values' => $values, 'title' => 'تنظیمات سایت'], 'admin');
}

function admin_settings_save(): never
{
    require_staff('content.manage');
    foreach (SETTING_FIELDS as $k => $_) {
        $v = trim((string) ($_POST[$k] ?? ''));
        if ($k === 'map_url' && $v !== '' && !preg_match('#^https://#i', $v)) {
            flash('error', 'لینک نقشه باید با https شروع شود.');
            continue;
        }
        if ($k === 'phone') {
            $v = normalize_digits($v);
        }
        db_query('DELETE FROM settings WHERE `key` = ?', [$k]);
        db_insert('settings', ['key' => $k, 'value' => $v]);
    }
    audit('update', 'settings');
    flash('success', 'تنظیمات ذخیره شد.');
    redirect('/admin/settings');
}
