<?php
declare(strict_types=1);

/**
 * بازه‌های کاری یک روز برای یک خدمت.
 * برنامه‌های اختصاصی خدمت اولویت دارند؛ اگر نبود از برنامه عمومی (service_id خالی) استفاده می‌شود.
 */
function day_schedules(array $service, string $ymd): array
{
    $exception = db_one('SELECT * FROM schedule_exceptions WHERE day = ?', [$ymd]);
    if ($exception && (int) $exception['is_closed'] === 1) {
        return [];
    }
    $weekday = fa_weekday_index($ymd);
    $rows = db_all('SELECT * FROM schedules WHERE weekday = ? AND service_id = ? ORDER BY start_time', [$weekday, $service['id']]);
    if (!$rows) {
        $rows = db_all('SELECT * FROM schedules WHERE weekday = ? AND service_id IS NULL ORDER BY start_time', [$weekday]);
    }
    if ($exception && $exception['start_time'] && $exception['end_time']) {
        // ساعت کاری متفاوت برای آن روز: بازه‌ها به ساعات استثنا محدود می‌شوند
        foreach ($rows as &$r) {
            $r['start_time'] = max($r['start_time'], $exception['start_time']);
            $r['end_time'] = min($r['end_time'], $exception['end_time']);
        }
        unset($r);
        $rows = array_values(array_filter($rows, fn ($r) => $r['start_time'] < $r['end_time']));
    }
    return $rows;
}

/** فهرست ساعت‌های خالی یک روز برای یک خدمت */
function available_slots(array $service, string $ymd): array
{
    $duration = max(5, (int) $service['duration_min']);
    $slots = [];
    $nowTs = time() + 30 * 60; // حداقل ۳۰ دقیقه بعد از الان
    foreach (day_schedules($service, $ymd) as $sch) {
        $start = strtotime($ymd . ' ' . $sch['start_time']);
        $end = strtotime($ymd . ' ' . $sch['end_time']);
        $capacity = max(1, (int) $sch['capacity']);
        for ($t = $start; $t + $duration * 60 <= $end; $t += $duration * 60) {
            if ($t < $nowTs) {
                continue;
            }
            $at = date('Y-m-d H:i:s', $t);
            if ($sch['service_id'] !== null) {
                $taken = (int) db_value(
                    "SELECT COUNT(*) FROM appointments WHERE starts_at = ? AND service_id = ? AND status NOT IN ('cancelled')",
                    [$at, $service['id']]
                );
            } else {
                // برنامه عمومی بین همه خدماتی که برنامه اختصاصی ندارند مشترک است
                $taken = (int) db_value(
                    "SELECT COUNT(*) FROM appointments a WHERE a.starts_at = ? AND a.status NOT IN ('cancelled')
                     AND NOT EXISTS (SELECT 1 FROM schedules s WHERE s.service_id = a.service_id)",
                    [$at]
                );
            }
            if ($taken < $capacity) {
                $slots[] = ['time' => date('H:i', $t), 'starts_at' => $at, 'left' => $capacity - $taken];
            }
        }
    }
    return $slots;
}

/** روزهای آینده که حداقل یک ساعت خالی دارند */
function available_days(array $service, ?int $daysAhead = null): array
{
    $daysAhead ??= (int) config('booking_days_ahead', 14);
    $days = [];
    for ($i = 0; $i <= $daysAhead; $i++) {
        $ymd = date('Y-m-d', strtotime("+{$i} day"));
        $count = count(available_slots($service, $ymd));
        if ($count > 0) {
            $days[] = ['date' => $ymd, 'count' => $count];
        }
    }
    return $days;
}

function new_tracking_code(): string
{
    do {
        $code = random_code(8, '23456789ABCDEFGHJKMNPQRSTUVWXYZ');
    } while (db_value('SELECT 1 FROM appointments WHERE tracking_code = ?', [$code]));
    return $code;
}
