<div class="box">
  <?php foreach ($rows as $r): ?>
    <form method="post" action="/admin/insurances" enctype="multipart/form-data" class="toolbar" style="border-bottom:1px solid var(--line);padding-bottom:12px">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
      <?php if ($r['logo_path']): ?><img src="<?= e(public_upload_url($r['logo_path'])) ?>" alt="" style="height:40px;width:auto"><?php endif; ?>
      <label class="field">نام<input name="name" value="<?= e($r['name']) ?>"></label>
      <label class="field">لوگو<input type="file" name="logo" accept="image/png,image/jpeg,image/webp"></label>
      <label class="field" style="flex-basis:90px">ترتیب<input name="sort_order" type="number" value="<?= (int) $r['sort_order'] ?>"></label>
      <label style="display:flex;gap:6px;align-items:center"><input type="checkbox" name="is_active" value="1" <?= (int) $r['is_active'] ? 'checked' : '' ?> style="width:auto"> فعال</label>
      <button class="btn btn-ghost btn-sm">ذخیره</button>
      <button class="btn btn-ghost btn-sm" formaction="/admin/insurances/<?= (int) $r['id'] ?>/delete" onclick="return confirm('حذف شود؟')">حذف</button>
    </form>
  <?php endforeach; ?>
  <form method="post" action="/admin/insurances" enctype="multipart/form-data" class="toolbar" style="margin-top:12px">
    <?= csrf_field() ?>
    <label class="field">بیمه جدید<input name="name" required></label>
    <label class="field">لوگو (اختیاری)<input type="file" name="logo" accept="image/png,image/jpeg,image/webp"></label>
    <input type="hidden" name="is_active" value="1">
    <button class="btn">افزودن</button>
  </form>
</div>
