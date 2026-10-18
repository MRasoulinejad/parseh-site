<div style="display:flex;flex-wrap:wrap;gap:18px;align-items:flex-start">
  <div class="box" style="flex:1 1 380px">
    <h2>مشخصات نوبت</h2>
    <dl class="kv">
      <dt>بیمار</dt><dd><?php if (staff_can('patients.view')): ?><a href="/admin/patients/<?= (int) $a['patient_id'] ?>"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></a><?php else: ?><?= e($a['first_name'] . ' ' . $a['last_name']) ?><?php endif; ?></dd>
      <dt>موبایل</dt><dd class="ltr" style="text-align:right"><?= e(fa_num($a['mobile'])) ?></dd>
      <dt>کد ملی</dt><dd><?= e(fa_num($a['national_code'] ?? '—')) ?></dd>
      <dt>خدمت</dt><dd><?= e($a['service_title']) ?></dd>
      <dt>زمان</dt><dd><?= jdate($a['starts_at'], 'long') ?> · <?= fa_num(jdate($a['starts_at'], 'time')) ?></dd>
      <dt>کد پیگیری</dt><dd class="ltr" style="text-align:right"><b><?= e($a['tracking_code']) ?></b></dd>
      <dt>بیمه</dt><dd><?= e($a['insurance_name'] ?? 'آزاد') ?></dd>
      <dt>ثبت</dt><dd><?= e(source_label($a['source'])) ?><?= $a['creator_name'] ? ' توسط ' . e($a['creator_name']) : '' ?> · <?= jdate($a['created_at'], 'datetime') ?></dd>
      <dt>وضعیت</dt><dd><span class="badge"><?= e(status_label($a['status'])) ?></span></dd>
    </dl>
    <?php if (staff_can('appointments.manage')): ?>
      <div class="actions" style="margin-top:16px">
        <?php foreach (['checked_in' => 'پذیرش شد', 'done' => 'انجام شد', 'no_show' => 'نیامد', 'cancelled' => 'لغو', 'confirmed' => 'بازگشت به تأییدشده'] as $s => $label): if ($s === $a['status']) continue; ?>
          <form method="post" action="/admin/appointments/<?= (int) $a['id'] ?>/status" class="inline"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $s ?>"><button class="btn btn-sm <?= in_array($s, ['cancelled', 'no_show'], true) ? 'btn-ghost' : '' ?>"><?= e($label) ?></button></form>
        <?php endforeach; ?>
      </div>
      <form method="post" action="/admin/appointments/<?= (int) $a['id'] ?>/notes" class="form" style="margin-top:16px">
        <?= csrf_field() ?>
        <label class="field">یادداشت داخلی<textarea name="notes" maxlength="500" style="min-height:70px"><?= e($a['notes']) ?></textarea></label>
        <button class="btn btn-ghost btn-sm" style="align-self:flex-start">ذخیره یادداشت</button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (staff_can('results.manage')): ?>
  <div class="box" style="flex:1 1 380px">
    <h2>جواب</h2>
    <?php if ($result): ?>
      <p style="margin:0 0 10px">وضعیت: <span class="badge <?= $result['status'] === 'ready' ? 'badge-ok' : 'badge-warn' ?>"><?= e(status_label($result['status'])) ?></span>
        <?php if ($result['ready_at']): ?> · منتشر شده <?= jdate($result['ready_at'], 'datetime') ?><?php endif; ?>
        <?php if ($result['first_viewed_at']): ?> · بیمار دیده: <?= jdate($result['first_viewed_at'], 'datetime') ?><?php endif; ?></p>
      <?php if ($result['report_path']): ?>
        <div class="actions" style="margin-bottom:10px">
          <a class="btn btn-ghost btn-sm" href="/results/<?= (int) $result['id'] ?>/report" target="_blank">مشاهده گزارش PDF</a>
          <form method="post" action="/admin/results/<?= (int) $result['id'] ?>/report/delete" class="inline" onsubmit="return confirm('گزارش حذف شود؟')"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">حذف گزارش</button></form>
        </div>
      <?php endif; ?>
      <?php if ($images): ?>
        <div class="gallery" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr))">
          <?php foreach ($images as $img): ?>
            <figure><img src="/results/<?= (int) $result['id'] ?>/image/<?= (int) $img['id'] ?>" alt="">
              <form method="post" action="/admin/result-images/<?= (int) $img['id'] ?>/delete" onsubmit="return confirm('تصویر حذف شود؟')"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" style="margin-top:4px">حذف</button></form></figure>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p class="muted" style="margin:0 0 10px">هنوز جوابی بارگذاری نشده است.</p>
    <?php endif; ?>

    <form method="post" action="/admin/results/<?= (int) $a['id'] ?>/upload" enctype="multipart/form-data" class="form" style="margin-top:14px">
      <?= csrf_field() ?>
      <label class="field">گزارش PDF<?= ($result && $result['report_path']) ? ' (جایگزین فعلی)' : '' ?><input type="file" name="report" accept="application/pdf"></label>
      <label class="field">تصاویر (JPG/PNG، حداکثر ۴ عدد)<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple></label>
      <button class="btn btn-ghost">بارگذاری</button>
    </form>

    <?php if ($result && ($result['report_path'] || $images)): ?>
      <div class="actions" style="margin-top:14px">
        <?php if ($result['status'] !== 'ready'): ?>
          <form method="post" action="/admin/results/<?= (int) $result['id'] ?>/publish" class="inline"><?= csrf_field() ?>
            <label style="display:flex;gap:6px;align-items:center;font-size:14px;margin-bottom:8px"><input type="checkbox" name="send_sms" value="1" checked style="width:auto"> پیامک «جواب آماده است» ارسال شود</label>
            <button class="btn">انتشار برای بیمار</button></form>
        <?php else: ?>
          <form method="post" action="/admin/results/<?= (int) $result['id'] ?>/unpublish" class="inline"><?= csrf_field() ?><button class="btn btn-ghost">برداشتن از دسترس بیمار</button></form>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
