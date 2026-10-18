<?php
// مسیر پوشه برنامه (بیرون از public_html). در صورت نیاز تغییر دهید.
$appPath = __DIR__ . '/../parseh_app';

require $appPath . '/bootstrap.php';
require $appPath . '/routes.php';

try {
    dispatch();
} catch (Throwable $e) {
    error_log((string) $e);
    if (config('debug')) {
        http_response_code(500);
        echo '<pre style="direction:ltr">' . e((string) $e) . '</pre>';
        exit;
    }
    abort(500, 'مشکلی پیش آمد. لطفاً دوباره تلاش کنید.');
}
