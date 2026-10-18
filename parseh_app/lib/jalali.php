<?php
declare(strict_types=1);

/** تبدیل میلادی به شمسی */
function g2j(int $gy, int $gm, int $gd): array
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * intdiv($days, 12053));
    $days %= 12053;
    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += intdiv($days - 1, 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + intdiv($days, 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intdiv($days - 186, 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return [$jy, $jm, $jd];
}

/** تبدیل شمسی به میلادی */
function j2g(int $jy, int $jm, int $jd): array
{
    $jy += 1595;
    $days = -355668 + (365 * $jy) + (intdiv($jy, 33) * 8) + intdiv(($jy % 33) + 3, 4) + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
    $gy = 400 * intdiv($days, 146097);
    $days %= 146097;
    if ($days > 36524) {
        $days--;
        $gy += 100 * intdiv($days, 36524);
        $days %= 36524;
        if ($days >= 365) {
            $days++;
        }
    }
    $gy += 4 * intdiv($days, 1461);
    $days %= 1461;
    if ($days > 365) {
        $gy += intdiv($days - 1, 365);
        $days = ($days - 1) % 365;
    }
    $gd = $days + 1;
    $leap = (($gy % 4 === 0) && ($gy % 100 !== 0)) || ($gy % 400 === 0);
    $sal_a = [0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    for ($gm = 0; $gm < 13 && $gd > $sal_a[$gm]; $gm++) {
        $gd -= $sal_a[$gm];
    }
    return [$gy, $gm, $gd];
}

function jalali_month_name(int $m): string
{
    return ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'][$m - 1] ?? '';
}

/** 0 = شنبه ... 6 = جمعه */
function fa_weekday_index(string $ymd): int
{
    return ((int) date('w', strtotime($ymd)) + 1) % 7;
}

function fa_weekday_name(int $index): string
{
    return ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'][$index] ?? '';
}

/**
 * نمایش تاریخ شمسی. فرمت‌ها:
 * date      → ۱۴۰۵/۰۷/۲۵
 * datetime  → ۱۴۰۵/۰۷/۲۵ ساعت ۱۰:۳۰
 * long      → شنبه ۲۵ مهر ۱۴۰۵
 * day       → ۲۵ مهر
 * time      → ۱۰:۳۰
 */
function jdate(?string $datetime, string $format = 'date'): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    [$jy, $jm, $jd] = g2j((int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts));
    $time = date('H:i', $ts);
    $out = match ($format) {
        'datetime' => sprintf('%04d/%02d/%02d', $jy, $jm, $jd) . ' ساعت ' . $time,
        'long'     => fa_weekday_name(fa_weekday_index(date('Y-m-d', $ts))) . ' ' . $jd . ' ' . jalali_month_name($jm) . ' ' . $jy,
        'day'      => $jd . ' ' . jalali_month_name($jm),
        'time'     => $time,
        'input'    => sprintf('%04d/%02d/%02d', $jy, $jm, $jd),
        default    => sprintf('%04d/%02d/%02d', $jy, $jm, $jd),
    };
    return $format === 'input' ? $out : fa_num($out);
}

/** '1405/07/25' (اعداد فارسی یا لاتین) → '2026-10-17' یا null */
function jalali_input_to_date(?string $value): ?string
{
    if (!$value) {
        return null;
    }
    $value = normalize_digits(trim($value));
    if (!preg_match('#^(\d{4})[/\-](\d{1,2})[/\-](\d{1,2})$#', $value, $m)) {
        return null;
    }
    [$jy, $jm, $jd] = [(int) $m[1], (int) $m[2], (int) $m[3]];
    if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
        return null;
    }
    [$gy, $gm, $gd] = j2g($jy, $jm, $jd);
    return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
}
