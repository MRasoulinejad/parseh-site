<div class="box">
  <h2>۱. انتخاب بیمار</h2>
  <?php if ($selected): ?>
    <p style="margin:0"><b><?= e($selected['first_name'] . ' ' . $selected['last_name']) ?></b> · <span class="ltr"><?= e(fa_num($selected['mobile'])) ?></span> · کد ملی <?= e(fa_num($selected['national_code'] ?? '—')) ?>
      <a href="/admin/appointments/new" class="hint">(تغییر)</a></p>
  <?php else: ?>
    <form method="get" class="toolbar" style="margin:0">
      <label class="field" style="flex-basis:300px">کد ملی، موبایل یا نام خانوادگی<input name="q" value="<?= e($q) ?>" autofocus></label>
      <button class="btn">جستجو</button>
      <?php if (staff_can('patients.manage')): ?><a class="btn btn-ghost" href="/admin/patients/new?back=appointment">بیمار جدید</a><?php endif; ?>
    </form>
    <?php if ($q !== ''): ?>
      <?php if (!$matches): ?><p class="muted">بیماری پیدا نشد.</p><?php endif; ?>
      <div class="choices">
        <?php foreach ($matches as $m): ?>
          <a class="choice" href="?patient_id=<?= (int) $m['id'] ?>"><b style="font-size:15px"><?= e($m['first_name'] . ' ' . $m['last_name']) ?></b><span class="ltr"><?= e(fa_num($m['mobile'])) ?></span></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php if ($selected): ?>
<form method="post" action="/admin/appointments/new" class="box form">
  <?= csrf_field() ?>
  <input type="hidden" name="patient_id" value="<?= (int) $selected['id'] ?>">
  <h2>۲. جزئیات نوبت</h2>
  <div class="form-row">
    <label class="field">خدمت<select name="service_id" required><?php foreach ($services as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['title']) ?></option><?php endforeach; ?></select></label>
    <label class="field">تاریخ<input name="date" value="<?= e(jdate(date('Y-m-d'), 'input')) ?>" required></label>
    <label class="field">ساعت<input type="time" name="time" required class="ltr"></label>
  </div>
  <div class="form-row">
    <label class="field">نوع ثبت<select name="source"><option value="phone">تلفنی</option><option value="walk_in">حضوری (پذیرش همین الان)</option></select></label>
    <label class="field">بیمه<select name="insurance_id"><option value="">بیمه پرونده / آزاد</option><?php foreach ($insurances as $i): ?><option value="<?= (int) $i['id'] ?>"><?= e($i['name']) ?></option><?php endforeach; ?></select></label>
  </div>
  <label class="field">یادداشت داخلی<textarea name="notes" maxlength="500" style="min-height:70px"></textarea></label>
  <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="send_sms" value="1" checked style="width:auto"> پیامک تأیید برای بیمار ارسال شود</label>
  <p class="hint" style="margin:0">ثبت از پنل محدودیت ظرفیت ندارد؛ قبل از ثبت، ساعت را با نوبت‌های همان روز چک کنید.</p>
  <button class="btn">ثبت نوبت</button>
</form>
<?php endif; ?>
