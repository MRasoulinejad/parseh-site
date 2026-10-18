<?php
declare(strict_types=1);

/**
 * ارسال ایمیل.
 * driver = smtp → از طریق یک حساب ایمیل هاست (پیشنهادی، احتمال رفتن به اسپم کمتر)
 * driver = mail → تابع mail() خود PHP
 * driver = log  → فقط ثبت در storage/logs/mail.log (تست)
 */
function mail_send(string $to, string $subject, string $html, string $text): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $driver = config('mail.driver', 'log');
    $fromAddr = (string) config('mail.from_address', 'no-reply@localhost');
    $fromName = (string) config('mail.from_name', config('app_name'));

    $boundary = 'b' . bin2hex(random_bytes(8));
    $headers = [
        'MIME-Version: 1.0',
        'From: ' . mail_encode_header($fromName) . " <{$fromAddr}>",
        "Reply-To: {$fromAddr}",
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'Date: ' . date('r'),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (explode('@', $fromAddr)[1] ?? 'localhost') . '>',
    ];
    $body = "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($text))
        . "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html))
        . "--{$boundary}--\r\n";
    $encodedSubject = mail_encode_header($subject);

    try {
        if ($driver === 'smtp') {
            return smtp_send($to, $fromAddr, $encodedSubject, $headers, $body);
        }
        if ($driver === 'mail') {
            return mail($to, $encodedSubject, $body, implode("\r\n", $headers), '-f' . $fromAddr);
        }
        return (bool) @file_put_contents(STORAGE_PATH . '/logs/mail.log', '[' . now() . "] to {$to}: {$subject}\n{$text}\n\n", FILE_APPEND);
    } catch (Throwable $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}

function mail_encode_header(string $s): string
{
    return '=?UTF-8?B?' . base64_encode($s) . '?=';
}

/** کلاینت ساده SMTP (SSL روی ۴۶۵ یا STARTTLS روی ۵۸۷) */
function smtp_send(string $to, string $from, string $subject, array $headers, string $body): bool
{
    $host = (string) config('mail.host', 'localhost');
    $port = (int) config('mail.port', 465);
    $enc = (string) config('mail.encryption', 'ssl');
    $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]]);
    $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        error_log("SMTP connect failed {$remote}: {$errstr}");
        return false;
    }
    stream_set_timeout($fp, 15);
    $read = function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    $cmd = function (string $c, array $okCodes) use ($fp, $read): bool {
        fwrite($fp, $c . "\r\n");
        $resp = $read();
        if (!in_array((int) substr($resp, 0, 3), $okCodes, true)) {
            error_log('SMTP error after "' . (str_starts_with($c, 'AUTH') || strlen($c) > 60 ? substr($c, 0, 10) . '…' : $c) . '": ' . trim($resp));
            return false;
        }
        return true;
    };

    $hostname = parse_url((string) config('app_url'), PHP_URL_HOST) ?: 'localhost';
    $ok = (int) substr($read(), 0, 3) === 220 && $cmd("EHLO {$hostname}", [250]);
    if ($ok && $enc === 'tls') {
        $ok = $cmd('STARTTLS', [220]) && stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) && $cmd("EHLO {$hostname}", [250]);
    }
    if ($ok && config('mail.username')) {
        $ok = $cmd('AUTH LOGIN', [334]) && $cmd(base64_encode((string) config('mail.username')), [334]) && $cmd(base64_encode((string) config('mail.password')), [235]);
    }
    $ok = $ok && $cmd("MAIL FROM:<{$from}>", [250]) && $cmd("RCPT TO:<{$to}>", [250, 251]) && $cmd('DATA', [354]);
    if ($ok) {
        $msg = "To: <{$to}>\r\nSubject: {$subject}\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $body;
        $msg = preg_replace('/^\./m', '..', str_replace(["\r\n", "\n"], ["\n", "\r\n"], $msg));
        $ok = $cmd($msg . "\r\n.", [250]);
    }
    @fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
}
