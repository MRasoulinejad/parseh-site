<?php
declare(strict_types=1);

function active_categories_with_services(): array
{
    $cats = db_all('SELECT * FROM service_categories ORDER BY sort_order, id');
    $services = db_all('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, id');
    foreach ($cats as &$c) {
        $c['services'] = array_values(array_filter($services, fn ($s) => (int) $s['category_id'] === (int) $c['id']));
    }
    unset($c);
    return array_values(array_filter($cats, fn ($c) => $c['services']));
}

function site_home(): string
{
    return view('site/home', [
        'categories' => active_categories_with_services(),
        'preps'      => db_all("SELECT title, slug, prep_text FROM services WHERE is_active = 1 AND prep_text IS NOT NULL AND prep_text <> '' ORDER BY sort_order LIMIT 6"),
        'insurances' => db_all('SELECT * FROM insurances WHERE is_active = 1 ORDER BY sort_order, id'),
    ], 'site');
}

function site_services(): string
{
    return view('site/services', ['categories' => active_categories_with_services(), 'title' => 'خدمات'], 'site');
}

function site_service(string $slug): string
{
    $service = db_one('SELECT s.*, c.title AS category_title, c.slug AS category_slug FROM services s JOIN service_categories c ON c.id = s.category_id WHERE s.slug = ? AND s.is_active = 1', [$slug]);
    if (!$service) {
        abort(404);
    }
    return view('site/service', [
        'service'     => $service,
        'gallery'     => db_all('SELECT * FROM gallery_items WHERE service_id = ? ORDER BY sort_order, id', [$service['id']]),
        'related'     => db_all('SELECT title, slug FROM services WHERE category_id = ? AND id <> ? AND is_active = 1 ORDER BY sort_order', [$service['category_id'], $service['id']]),
        'title'       => $service['title'],
        'description' => $service['summary'],
    ], 'site');
}

function site_posts(): string
{
    $posts = db_all('SELECT id, title, slug, cover_path, published_at, body FROM posts WHERE published_at IS NOT NULL AND published_at <= ? ORDER BY published_at DESC', [now()]);
    return view('site/posts', ['posts' => $posts, 'title' => 'مقالات'], 'site');
}

function site_post(string $slug): string
{
    $post = db_one('SELECT * FROM posts WHERE slug = ? AND published_at IS NOT NULL AND published_at <= ?', [$slug, now()]);
    if (!$post) {
        abort(404);
    }
    return view('site/post', ['post' => $post, 'title' => $post['title']], 'site');
}

function site_contact(): string
{
    return view('site/contact', ['title' => 'تماس با ما'], 'site');
}

function site_contact_send(): never
{
    $name = (string) input('name', '');
    $mobile = (string) input('mobile', '');
    $message = (string) input('message', '');
    if (!empty($_POST['website'])) { // تله ربات
        redirect('/contact');
    }
    if (mb_strlen($name) < 2 || mb_strlen($message) < 5 || ($mobile !== '' && !valid_mobile($mobile))) {
        with_old($_POST);
        flash('error', 'نام، پیام و شماره موبایل معتبر را وارد کنید.');
        redirect('/contact');
    }
    if (!rate_limit('contact:' . client_ip(), 5, 3600)) {
        flash('error', 'تعداد پیام‌ها زیاد است؛ کمی بعد تلاش کنید.');
        redirect('/contact');
    }
    db_insert('contact_messages', [
        'name'       => mb_substr($name, 0, 100),
        'mobile'     => $mobile ?: null,
        'message'    => mb_substr($message, 0, 3000),
        'is_read'    => 0,
        'created_at' => now(),
    ]);
    flash('success', 'پیام شما ثبت شد. همکاران ما در اولین فرصت پاسخ می‌دهند.');
    redirect('/contact');
}

/* ---------- جواب آنلاین با کد ملی و کد پیگیری ---------- */

function site_results(): string
{
    $result = null;
    $id = (int) ($_SESSION['result_access_last'] ?? 0);
    if ($id && !empty($_SESSION['result_access'][$id])) {
        $result = result_bundle($id);
    }
    return view('site/results', ['result' => $result, 'title' => 'دریافت جواب آنلاین'], 'site');
}

function site_results_lookup(): never
{
    $nc = (string) input('national_code', '');
    $code = strtoupper((string) input('tracking_code', ''));
    if (!rate_limit('result-lookup:' . client_ip(), 10, 900)) {
        flash('error', 'تعداد تلاش‌ها زیاد است. ۱۵ دقیقه دیگر دوباره امتحان کنید.');
        redirect('/results');
    }
    $row = db_one(
        "SELECT r.id, r.status FROM results r
         JOIN appointments a ON a.id = r.appointment_id
         JOIN patients p ON p.id = a.patient_id
         WHERE p.national_code = ? AND a.tracking_code = ?",
        [$nc, $code]
    );
    if (!$row) {
        flash('error', 'جوابی با این کد ملی و کد پیگیری پیدا نشد. اگر تازه مراجعه کرده‌اید، ممکن است جواب هنوز آماده نشده باشد.');
        redirect('/results');
    }
    if ($row['status'] !== 'ready') {
        flash('info', $row['status'] === 'expired' ? 'مهلت نگهداری این جواب روی سایت تمام شده است. برای دریافت نسخه به کلینیک مراجعه کنید.' : 'جواب شما هنوز آماده نشده است. پس از آماده شدن پیامک دریافت می‌کنید.');
        redirect('/results');
    }
    $_SESSION['result_access'][(int) $row['id']] = true;
    $_SESSION['result_access_last'] = (int) $row['id'];
    redirect('/results');
}

function result_bundle(int $resultId): ?array
{
    $r = db_one(
        "SELECT r.*, a.starts_at, a.tracking_code, a.patient_id, s.title AS service_title, p.first_name, p.last_name
         FROM results r JOIN appointments a ON a.id = r.appointment_id
         JOIN services s ON s.id = a.service_id JOIN patients p ON p.id = a.patient_id
         WHERE r.id = ?",
        [$resultId]
    );
    if (!$r) {
        return null;
    }
    $r['images'] = db_all('SELECT * FROM result_images WHERE result_id = ? ORDER BY sort_order, id', [$resultId]);
    return $r;
}

/** آیا بیننده فعلی اجازه دیدن این جواب را دارد؟ */
function can_view_result(array $r): bool
{
    if (staff_can('results.manage') || staff_can('patients.view')) {
        return true;
    }
    if ($r['status'] !== 'ready') {
        return false;
    }
    if (!empty($_SESSION['result_access'][(int) $r['id']])) {
        return true;
    }
    $p = patient();
    return $p && (int) $p['id'] === (int) $r['patient_id'];
}

function site_result_file(string $id, string $kind, string $fileId = '0'): never
{
    $r = result_bundle((int) $id);
    if (!$r || !can_view_result($r)) {
        abort(404);
    }
    if (!staff()) {
        if (!$r['first_viewed_at']) {
            db_update('results', ['first_viewed_at' => now()], 'id = ?', [$r['id']]);
        }
    }
    audit('view_result', 'results', (int) $r['id']);
    if ($kind === 'report' && $r['report_path']) {
        send_private_file($r['report_path'], 'report-' . $r['tracking_code'] . '.pdf');
    }
    if ($kind === 'image') {
        foreach ($r['images'] as $img) {
            if ((int) $img['id'] === (int) $fileId) {
                send_private_file($img['file_path'], 'image-' . $img['id'] . '.' . pathinfo($img['file_path'], PATHINFO_EXTENSION));
            }
        }
    }
    abort(404);
}
