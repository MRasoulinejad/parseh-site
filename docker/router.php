<?php
// مسیریاب سرور داخلی PHP برای نسخه نمایشی
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . '/../public_html' . $path;
if ($path !== '/' && is_file($file) && !str_ends_with($file, '.php') && !str_contains($path, '..')) {
    return false;
}
require __DIR__ . '/../public_html/index.php';
