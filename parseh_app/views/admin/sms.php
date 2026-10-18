<?php $tpl = ['otp' => 'کد ورود', 'confirm' => 'تأیید نوبت', 'reminder' => 'یادآوری', 'result_ready' => 'جواب آماده']; ?>
<p class="hint">متن پیامک‌ها برای حفظ حریم بیماران ذخیره نمی‌شود.<?= config('sms.driver') === 'log' ? ' سامانه پیامک هنوز وصل نشده و پیامک‌ها فقط در فایل لاگ ثبت می‌شوند.' : '' ?></p>
<div class="table-wrap"><table>
  <thead><tr><th>زمان</th><th>موبایل</th><th>نوع</th><th>وضعیت</th></tr></thead>
  <tbody><?php foreach ($page['rows'] as $s): ?>
    <tr><td><?= jdate($s['created_at'], 'datetime') ?></td><td class="ltr"><?= e(fa_num($s['mobile'])) ?></td><td><?= e($tpl[$s['template']] ?? $s['template']) ?></td>
      <td><span class="badge <?= $s['status'] === 'sent' ? 'badge-ok' : ($s['status'] === 'failed' ? 'badge-err' : '') ?>"><?= ['sent' => 'ارسال شد', 'failed' => 'ناموفق', 'queued' => 'در صف'][$s['status']] ?? e($s['status']) ?></span></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php partial('admin/_pagination', ['page' => $page]); ?>
