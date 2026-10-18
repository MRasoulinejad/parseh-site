<?php
declare(strict_types=1);

/**
 * ارسال پیامک.
 *
 * driver = log      → فقط ثبت در storage/logs/sms.log (تست)
 * driver = smsir    → وب‌سرویس SMS.ir (api.sms.ir/v1)
 *                      اگر برای این نوع پیام templateId تعریف شده باشد، از «ارسال سریع/وریفای» با پارامترها استفاده می‌شود
 *                      (برای کد ورود الزامی است)؛ وگرنه متن کامل از خط اختصاصی با send/bulk فرستاده می‌شود.
 * driver = kavenegar → وب‌سرویس کاوه‌نگار
 *
 * $template: otp | confirm | reminder | result_ready | test
 * $params:   مقادیر پارامترهای قالب، مثلاً ['CODE' => '12345']
 */
function sms_send(string $mobile, string $template, string $text, array $params = []): bool
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
        if ($driver === 'smsir') {
            [$ok, $providerId] = smsir_send($mobile, $template, $text, $params);
        } elseif ($driver === 'kavenegar') {
            [$ok, $providerId] = kavenegar_send($mobile, $text);
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

/* ---------------- SMS.ir ---------------- */

/** درخواست به api.sms.ir/v1 ؛ خروجی: [کد HTTP، بدنه JSON] */
function smsir_request(string $method, string $path, ?array $body = null): array
{
    $ch = curl_init(rtrim((string) config('sms.smsir_base_url', 'https://api.sms.ir/v1'), '/') . '/' . ltrim($path, '/'));
    $opts = [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            'X-API-KEY: ' . (string) config('sms.smsir_api_key'),
        ],
    ];
    if ($body !== null) {
        $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
    }
    curl_setopt_array($ch, $opts);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        error_log('SMS.ir connection error: ' . $err);
        return [0, null];
    }
    $json = json_decode((string) $raw, true);
    if ($status !== 200 || (int) ($json['status'] ?? 0) !== 1) {
        error_log('SMS.ir error ' . $status . ': ' . mb_substr((string) $raw, 0, 300));
    }
    return [$status, is_array($json) ? $json : null];
}

function smsir_send(string $mobile, string $template, string $text, array $params): array
{
    $templateId = (int) (config('sms.smsir_templates.' . $template) ?? 0);

    if ($templateId > 0) {
        // ارسال سریع با قالب تأییدشده (پارامترها حداکثر ۲۵ کاراکتر)
        $parameters = [];
        foreach ($params as $name => $value) {
            $parameters[] = ['name' => (string) $name, 'value' => mb_substr(trim((string) $value), 0, 25)];
        }
        [$status, $json] = smsir_request('POST', 'send/verify', [
            'mobile'     => $mobile,
            'templateId' => $templateId,
            'parameters' => $parameters,
        ]);
        $ok = $status === 200 && (int) ($json['status'] ?? 0) === 1;
        $id = isset($json['data']['messageId']) ? (string) $json['data']['messageId'] : null;
        return [$ok, $id];
    }

    if ($template === 'otp') {
        // کد ورود بدون قالب از خط تبلیغاتی ممکن است به مشترکین بلک‌لیست نرسد
        error_log('SMS.ir: no templateId configured for otp; falling back to bulk send');
    }

    $line = (string) config('sms.smsir_line');
    if ($line === '') {
        error_log('SMS.ir: smsir_line is empty');
        return [false, null];
    }
    [$status, $json] = smsir_request('POST', 'send/bulk', [
        'lineNumber'   => (int) $line,
        'messageText'  => $text,
        'mobiles'      => [$mobile],
        'sendDateTime' => null,
    ]);
    $ok = $status === 200 && (int) ($json['status'] ?? 0) === 1;
    $id = isset($json['data']['packId']) ? (string) $json['data']['packId'] : null;
    return [$ok, $id];
}

/** اعتبار باقی‌مانده پنل SMS.ir (یا null) */
function smsir_credit(): ?float
{
    if (config('sms.driver') !== 'smsir' || !config('sms.smsir_api_key')) {
        return null;
    }
    [$status, $json] = smsir_request('GET', 'credit');
    return ($status === 200 && isset($json['data']) && is_numeric($json['data'])) ? (float) $json['data'] : null;
}

/* ---------------- کاوه‌نگار ---------------- */

function kavenegar_send(string $mobile, string $text): array
{
    $url = 'https://api.kavenegar.com/v1/' . rawurlencode((string) config('sms.kavenegar_key')) . '/sms/send.json';
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
    return [$ok, isset($json['entries'][0]['messageid']) ? (string) $json['entries'][0]['messageid'] : null];
}
