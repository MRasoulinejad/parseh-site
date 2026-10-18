<?php
declare(strict_types=1);

define('APP_PATH', __DIR__);
define('STORAGE_PATH', __DIR__ . '/storage');

if (!is_file(APP_PATH . '/config.php')) {
    http_response_code(500);
    exit('config.php پیدا نشد. فایل config.sample.php را به config.php کپی و تنظیم کنید.');
}
$GLOBALS['__config'] = require APP_PATH . '/config.php';

date_default_timezone_set($GLOBALS['__config']['timezone'] ?? 'Asia/Tehran');
mb_internal_encoding('UTF-8');

if (!empty($GLOBALS['__config']['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');

foreach (['helpers', 'jalali', 'db', 'auth', 'sms', 'upload', 'slots', 'router', 'icons'] as $lib) {
    require APP_PATH . "/lib/{$lib}.php";
}

if (PHP_SAPI !== 'cli') {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name('parseh_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
}
