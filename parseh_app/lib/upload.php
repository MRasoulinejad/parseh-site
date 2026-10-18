<?php
declare(strict_types=1);

const UPLOAD_TYPES = [
    'application/pdf' => 'pdf',
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
];

/**
 * یک فایل آپلودشده را با بررسی نوع واقعی ذخیره می‌کند.
 * $private = true → داخل storage (غیرقابل دسترسی مستقیم)، وگرنه داخل public_html/uploads
 * خروجی: مسیر نسبی فایل یا null
 */
function store_upload(array $file, string $folder, array $allowedExt, bool $private, int $maxMb = 10): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }
    if ($file['size'] > $maxMb * 1024 * 1024) {
        flash('error', "حجم فایل «{$file['name']}» بیشتر از {$maxMb} مگابایت است.");
        return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext = UPLOAD_TYPES[$mime] ?? null;
    if (!$ext || !in_array($ext, $allowedExt, true)) {
        flash('error', "نوع فایل «{$file['name']}» مجاز نیست.");
        return null;
    }
    $relative = trim($folder, '/') . '/' . date('Y/m') . '/' . bin2hex(random_bytes(12)) . '.' . $ext;
    $base = $private ? STORAGE_PATH . '/private' : rtrim(config('public_uploads_dir'), '/');
    $target = $base . '/' . $relative;
    if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0755, true) && !is_dir(dirname($target))) {
        flash('error', 'پوشه آپلود قابل ساخت نیست.');
        return null;
    }
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return null;
    }
    return $relative;
}

/** فایل‌های چندتایی input[name][] را به آرایه‌ای از فایل‌ها تبدیل می‌کند */
function normalize_files(?array $files): array
{
    if (!$files || !is_array($files['name'] ?? null)) {
        return $files && ($files['error'] ?? 4) !== UPLOAD_ERR_NO_FILE ? [$files] : [];
    }
    $out = [];
    foreach ($files['name'] as $i => $name) {
        if (($files['error'][$i] ?? 4) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $out[] = [
            'name'     => $name,
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];
    }
    return $out;
}

function private_path(string $relative): string
{
    return STORAGE_PATH . '/private/' . ltrim($relative, '/');
}

function delete_private(?string $relative): void
{
    if ($relative && is_file(private_path($relative))) {
        @unlink(private_path($relative));
    }
}

function delete_public(?string $relative): void
{
    $path = rtrim(config('public_uploads_dir'), '/') . '/' . ltrim((string) $relative, '/');
    if ($relative && is_file($path)) {
        @unlink($path);
    }
}

function send_private_file(string $relative, string $downloadName): never
{
    $path = private_path($relative);
    if (!is_file($path)) {
        abort(404, 'فایل پیدا نشد؛ ممکن است مهلت نگهداری آن تمام شده باشد.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: inline; filename="' . rawurlencode($downloadName) . '"');
    header('Cache-Control: private, no-store');
    readfile($path);
    exit;
}
