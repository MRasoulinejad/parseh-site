<?php
declare(strict_types=1);

function config(string $key, $default = null)
{
    $value = $GLOBALS['__config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function public_upload_url(?string $file): string
{
    if (!$file) {
        return '';
    }
    return rtrim(config('public_uploads_url'), '/') . '/' . ltrim($file, '/');
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function back(): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '/';
    $host = parse_url($ref, PHP_URL_HOST);
    if ($host && $host !== ($_SERVER['HTTP_HOST'] ?? '')) {
        $ref = '/';
    }
    redirect($ref);
}

function abort(int $code = 404, string $message = ''): never
{
    http_response_code($code);
    $titles = [403 => 'دسترسی ندارید', 404 => 'صفحه پیدا نشد', 429 => 'تعداد درخواست‌ها زیاد است', 500 => 'خطای سرور'];
    echo view('site/error', ['code' => $code, 'title' => $titles[$code] ?? 'خطا', 'message' => $message], 'site');
    exit;
}

/** رندر یک view داخل layout */
function view(string $name, array $data = [], ?string $layout = null): string
{
    $file = APP_PATH . '/views/' . $name . '.php';
    if (!is_file($file)) {
        throw new RuntimeException("View not found: {$name}");
    }
    extract($data, EXTR_SKIP);
    ob_start();
    require $file;
    $content = ob_get_clean();
    if ($layout === null) {
        return $content;
    }
    ob_start();
    require APP_PATH . '/views/layouts/' . $layout . '.php';
    return ob_get_clean();
}

function partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require APP_PATH . '/views/' . $name . '.php';
}

/* ---------- CSRF / flash / old input ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        abort(403, 'نشست شما منقضی شده است. صفحه را دوباره باز کنید.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function with_old(array $input): void
{
    $_SESSION['_old'] = $input;
}

function old(string $key, $default = '')
{
    static $old = null;
    if ($old === null) {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
    }
    return $old[$key] ?? $default;
}

function input(string $key, $default = null)
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    if (is_string($v)) {
        $v = trim(normalize_digits($v));
    }
    return $v;
}

function input_int(string $key): ?int
{
    $v = input($key);
    return ($v === null || $v === '' || !is_numeric($v)) ? null : (int) $v;
}

/* ---------- اعداد و اعتبارسنجی ---------- */

function normalize_digits(string $s): string
{
    $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $ar = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($ar, $en, str_replace($fa, $en, $s));
}

function fa_num($s): string
{
    return str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $s);
}

function valid_mobile(string $m): bool
{
    return (bool) preg_match('/^09\d{9}$/', $m);
}

function valid_national_code(string $code): bool
{
    if (!preg_match('/^\d{10}$/', $code) || preg_match('/^(\d)\1{9}$/', $code)) {
        return false;
    }
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += (int) $code[$i] * (10 - $i);
    }
    $r = $sum % 11;
    $check = (int) $code[9];
    return ($r < 2 && $check === $r) || ($r >= 2 && $check === 11 - $r);
}

function random_code(int $length, string $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'): string
{
    $out = '';
    $max = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; $i++) {
        $out .= $alphabet[random_int(0, $max)];
    }
    return $out;
}

function slugify(string $text): string
{
    $text = trim(mb_strtolower($text));
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
    return trim($text, '-') ?: bin2hex(random_bytes(4));
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db_all('SELECT `key`, `value` FROM settings') as $row) {
                $cache[$row['key']] = (string) $row['value'];
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return ($cache[$key] ?? '') !== '' ? $cache[$key] : $default;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/** ثبت در audit_logs */
function audit(string $action, string $entity, ?int $entityId = null): void
{
    $user = staff();
    db_insert('audit_logs', [
        'user_id'    => $user['id'] ?? null,
        'patient_id' => (!$user && patient()) ? patient()['id'] : null,
        'action'     => $action,
        'entity'     => $entity,
        'entity_id'  => $entityId,
        'ip'         => client_ip(),
        'created_at' => now(),
    ]);
}

/** محدودیت تعداد تلاش؛ اگر مجاز باشد true */
function rate_limit(string $key, int $max, int $windowSeconds): bool
{
    $row = db_one('SELECT * FROM rate_limits WHERE `key` = ?', [$key]);
    $nowTs = time();
    if (!$row || strtotime($row['reset_at']) <= $nowTs) {
        db_query('DELETE FROM rate_limits WHERE `key` = ?', [$key]);
        db_insert('rate_limits', ['key' => $key, 'hits' => 1, 'reset_at' => date('Y-m-d H:i:s', $nowTs + $windowSeconds)]);
        return true;
    }
    if ((int) $row['hits'] >= $max) {
        return false;
    }
    db_query('UPDATE rate_limits SET hits = hits + 1 WHERE `key` = ?', [$key]);
    return true;
}

/** صفحه‌بندی ساده */
function paginate(string $sql, array $params = [], int $perPage = 25): array
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $count = (int) db_value("SELECT COUNT(*) FROM ({$sql}) AS t", $params);
    $rows = db_all($sql . ' LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage), $params);
    return ['rows' => $rows, 'page' => $page, 'pages' => max(1, (int) ceil($count / $perPage)), 'total' => $count];
}

function page_url(int $page): string
{
    $q = $_GET;
    $q['page'] = $page;
    return strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($q);
}

function status_label(string $status): string
{
    return [
        'pending'    => 'در انتظار تأیید',
        'confirmed'  => 'تأیید شده',
        'checked_in' => 'پذیرش شده',
        'done'       => 'انجام شده',
        'cancelled'  => 'لغو شده',
        'no_show'    => 'مراجعه نکرد',
        'draft'      => 'پیش‌نویس',
        'ready'      => 'آماده',
        'expired'    => 'منقضی',
    ][$status] ?? $status;
}

function source_label(string $source): string
{
    return ['online' => 'آنلاین', 'phone' => 'تلفنی', 'walk_in' => 'حضوری'][$source] ?? $source;
}
