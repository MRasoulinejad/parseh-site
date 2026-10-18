<?php
$tpl = ['otp' => 'کد ورود', 'confirm' => 'تأیید نوبت', 'reminder' => 'یادآوری', 'result_ready' => 'جواب آماده', 'test' => 'آزمایشی'];
$driver = config('sms.driver', 'log');
$driverName = ['smsir' => 'SMS.ir', 'kavenegar' => 'کاوه‌نگار', 'log' => 'غیرفعال (فقط ثبت در لاگ)'][$driver] ?? $driver;
$tplIds = (array) config('sms.smsir_templates', []);
?>
<div class="stats">
  <div class="stat"><b style="font-size:20px"><?= e($driverName) ?></b><span>سامانه پیامک</span></div>
  <?php if ($driver === 'smsir'): ?>
    <div class="stat"><b><?= $credit !== null ? fa_num(number_format($credit)) : '—' ?></b><span>اعتبار باقی‌مانده<?= $credit === null ? ' (خواندن اعتبار ممکن نشد)' : '' ?></span></div>
    <div class="stat"><b style="font-size:20px"><?= fa_num((string) count(array_filter($tplIds))) ?> از ۴</b><span>قالب ارسال سریع تنظیم شده</span></div>
  <?php endif; ?>
</div>

<?php if ($driver === 'smsir' && empty($tplIds['otp'])): ?>
  <div class="alert alert-info">قالب «کد ورود» (otp) تنظیم نشده است. بدون قالب، کد ورود از خط عادی فرستاده می‌شود و ممکن است به مشترکینی که پیامک تبلیغاتی را بسته‌اند نرسد.</div>
<?php endif; ?>

<form method="post" action="/admin/sms/test" class="box toolbar">
  <?= csrf_field() ?>
  <label class="field">ارسال پیامک آزمایشی به<input name="mobile" value="<?= e(staff()['mobile']) ?>" class="ltr" maxlength="11"></label>
  <button class="btn">ارسال آزمایشی</button>
</form>

<p class="hint">متن پیامک‌ها برای حفظ حریم بیماران ذخیره نمی‌شود.</p>
<div class="table-wrap"><table>
  <thead><tr><th>زمان</th><th>موبایل</th><th>نوع</th><th>وضعیت</th><th>شناسه سامانه</th></tr></thead>
  <tbody><?php foreach ($page['rows'] as $s): ?>
    <tr><td><?= jdate($s['created_at'], 'datetime') ?></td><td class="ltr"><?= e(fa_num($s['mobile'])) ?></td><td><?= e($tpl[$s['template']] ?? $s['template']) ?></td>
      <td><span class="badge <?= $s['status'] === 'sent' ? 'badge-ok' : ($s['status'] === 'failed' ? 'badge-err' : '') ?>"><?= ['sent' => 'ارسال شد', 'failed' => 'ناموفق', 'queued' => 'در صف'][$s['status']] ?? e($s['status']) ?></span></td>
      <td class="ltr"><?= e($s['provider_id'] ?? '') ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php partial('admin/_pagination', ['page' => $page]); ?>
