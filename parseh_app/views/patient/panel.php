<section class="wrap section" style="max-width:960px">
  <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:24px">
    <div>
      <h1 style="font-size:30px;font-weight:800"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></h1>
      <span class="muted ltr"><?= e(fa_num($p['mobile'])) ?></span>
    </div>
    <div style="display:flex;gap:8px">
      <a class="btn" href="/booking">نوبت جدید</a>
      <form method="post" action="/logout"><?= csrf_field() ?><button class="btn btn-ghost">خروج</button></form>
    </div>
  </div>

  <h2 style="font-size:22px;font-weight:800;margin-bottom:12px">جواب‌های آماده</h2>
  <?php if (!$results): ?>
    <p class="muted">هنوز جوابی آماده نشده است. وقتی جواب آماده شود پیامک دریافت می‌کنید.</p>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:12px">
      <?php foreach ($results as $r): partial('site/_result_card', ['r' => $r]); endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($messages)): ?>
    <h2 style="font-size:22px;font-weight:800;margin:28px 0 12px">پیام‌های من</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>تاریخ</th><th>پیام</th><th>وضعیت</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($messages as $msg): ?>
          <tr>
            <td><?= jdate($msg['created_at']) ?></td>
            <td><?= e(mb_substr($msg['message'], 0, 60)) ?><?= mb_strlen($msg['message']) > 60 ? '…' : '' ?></td>
            <td><?= $msg['reply_text'] ? '<span class="badge badge-ok">پاسخ داده شد</span>' : '<span class="badge">در انتظار پاسخ</span>' ?></td>
            <td><a class="btn btn-ghost btn-sm" href="/messages/<?= e($msg['tracking_code']) ?>">مشاهده</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <h2 style="font-size:22px;font-weight:800;margin:28px 0 12px">نوبت‌های من</h2>
  <?php if (!$appointments): ?>
    <p class="muted">نوبتی ثبت نکرده‌اید.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>خدمت</th><th>زمان</th><th>کد پیگیری</th><th>وضعیت</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a):
            $canCancel = in_array($a['status'], ['pending', 'confirmed'], true) && strtotime($a['starts_at']) > time() + 3600 * $cancelBefore; ?>
          <tr>
            <td><?= e($a['service_title']) ?></td>
            <td><?= jdate($a['starts_at'], 'datetime') ?></td>
            <td class="ltr"><?= e($a['tracking_code']) ?></td>
            <td><span class="badge <?= $a['status'] === 'cancelled' ? 'badge-err' : ($a['status'] === 'done' ? 'badge-ok' : '') ?>"><?= e(status_label($a['status'])) ?></span></td>
            <td>
              <?php if ($canCancel): ?>
                <form method="post" action="/panel/appointments/<?= (int) $a['id'] ?>/cancel" onsubmit="return confirm('این نوبت لغو شود؟')"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">لغو</button></form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="hint">لغو آنلاین تا <?= fa_num($cancelBefore) ?> ساعت قبل از نوبت ممکن است.</p>
  <?php endif; ?>
</section>
