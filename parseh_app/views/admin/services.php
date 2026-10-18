<div class="box">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <h2 style="margin:0">خدمات</h2>
    <a class="btn btn-sm" href="/admin/services/new">خدمت جدید</a>
  </div>
  <div class="table-wrap" style="margin-top:12px"><table>
    <thead><tr><th>عنوان</th><th>دسته</th><th>مدت (دقیقه)</th><th>رزرو آنلاین</th><th>نمایش</th><th></th></tr></thead>
    <tbody><?php foreach ($services as $s): ?>
      <tr><td><?= e($s['title']) ?></td><td><?= e($s['category_title']) ?></td><td><?= fa_num($s['duration_min']) ?></td>
        <td><?= (int) $s['is_bookable'] ? '<span class="badge badge-ok">بله</span>' : '<span class="badge">تلفنی</span>' ?></td>
        <td><?= (int) $s['is_active'] ? '<span class="badge badge-ok">فعال</span>' : '<span class="badge badge-err">مخفی</span>' ?></td>
        <td class="actions"><a class="btn btn-ghost btn-sm" href="/admin/services/<?= (int) $s['id'] ?>/edit">ویرایش</a><a class="btn btn-ghost btn-sm" href="/services/<?= e($s['slug']) ?>" target="_blank">مشاهده</a></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
</div>

<div class="box">
  <h2>دسته‌ها</h2>
  <div class="table-wrap"><table>
    <thead><tr><th>عنوان</th><th>نشانی</th><th>ترتیب</th><th>تعداد خدمت</th><th></th></tr></thead>
    <tbody><?php foreach ($categories as $c): ?>
      <tr>
        <td colspan="3">
          <form method="post" action="/admin/categories" class="form-row" style="align-items:center"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <input name="title" value="<?= e($c['title']) ?>" aria-label="عنوان"><input name="slug" value="<?= e($c['slug']) ?>" class="ltr" aria-label="نشانی"><input name="sort_order" value="<?= (int) $c['sort_order'] ?>" style="max-width:80px" aria-label="ترتیب"><button class="btn btn-ghost btn-sm" style="flex:0 0 auto">ذخیره</button></form>
        </td>
        <td><?= fa_num($c['n']) ?></td>
        <td><?php if (!(int) $c['n']): ?><form method="post" action="/admin/categories/<?= (int) $c['id'] ?>/delete" onsubmit="return confirm('حذف شود؟')"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">حذف</button></form><?php endif; ?></td>
      </tr>
    <?php endforeach; ?></tbody>
  </table></div>
  <form method="post" action="/admin/categories" class="toolbar" style="margin-top:14px"><?= csrf_field() ?>
    <label class="field">دسته جدید<input name="title" required></label>
    <label class="field">نشانی انگلیسی (اختیاری)<input name="slug" class="ltr"></label>
    <button class="btn">افزودن دسته</button>
  </form>
  <p class="hint">نشانی‌های pregnancy، breast، pediatric، doppler، general-ultrasound و radiology آیکون اختصاصی دارند.</p>
</div>
