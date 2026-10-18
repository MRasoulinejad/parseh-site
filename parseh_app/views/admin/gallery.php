<form method="post" action="/admin/gallery" enctype="multipart/form-data" class="box toolbar">
  <?= csrf_field() ?>
  <label class="field">خدمت<select name="service_id"><option value="">عمومی</option><?php foreach ($services as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['title']) ?></option><?php endforeach; ?></select></label>
  <label class="field">توضیح تصویر<input name="caption" maxlength="200"></label>
  <label class="field">تصاویر<input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" required></label>
  <button class="btn">بارگذاری</button>
</form>
<div class="alert alert-info">پیش از بارگذاری، نام بیمار، تاریخ و هر مشخصه‌ای را که روی تصویر چاپ شده حذف کنید.</div>
<div class="gallery">
  <?php foreach ($items as $g): ?>
    <figure class="box" style="padding:10px;margin:0">
      <img src="<?= e(public_upload_url($g['file_path'])) ?>" alt="">
      <figcaption><?= e($g['service_title'] ?? 'عمومی') ?><?= $g['caption'] ? ' · ' . e($g['caption']) : '' ?></figcaption>
      <form method="post" action="/admin/gallery/<?= (int) $g['id'] ?>/delete" onsubmit="return confirm('حذف شود؟')"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" style="margin-top:6px">حذف</button></form>
    </figure>
  <?php endforeach; ?>
</div>
