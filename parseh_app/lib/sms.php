<?php
declare(strict_types=1);

/**
 * ارسال پیامک. driver = log فقط پیام را ثبت می‌کند (برای تست).
 * driver = kavenegar از وب‌سرویس کاوه‌نگار استفاده می‌کند.
 */
function sms_send(string $mobile, string $template, string $text): bool
{
    $logId = db_insert('sms_logs', [
        'mobile'     => $mobile,
        'template'   => $template,
        'status'     => 'queued',
        'created_at' => now(),
    ]);

    $driver = config('sms.driver', 'log');
    $ok = false;
    $providerId = null;

    try {
        if ($driver === 'kavenegar') {
            $key = config('sms.kavenegar_key');
            $url = 'https://api.kavenegar.com/v1/' . rawurlencode((string) $key) . '/sms/send.json';
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query(array_filter([
                    'receptor' => $mobile,
                    'message'  => $text,
                    'sender'   => config('sms.sender') ?: null,
                ])),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
            ]);
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $json = is_string($body) ? json_decode($body, true) : null;
            $ok = $status === 200 && (($json['return']['status'] ?? 0) == 200);
            $providerId = isset($json['entries'][0]['messageid']) ? (string) $json['entries'][0]['messageid'] : null;
        } else {
            $line = '[' . now() . "] {$mobile} ({$template}): {$text}\n";
            $ok = (bool) @file_put_contents(STORAGE_PATH . '/logs/sms.log', $line, FILE_APPEND);
        }
    } catch (Throwable $e) {
        error_log('SMS error: ' . $e->getMessage());
        $ok = false;
    }

    db_update('sms_logs', ['status' => $ok ? 'sent' : 'failed', 'provider_id' => $providerId], 'id = ?', [$logId]);
    return $ok;
}
