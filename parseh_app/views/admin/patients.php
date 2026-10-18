<form method="get" class="toolbar">
  <label class="field" style="flex-basis:300px">جستجو (نام، موبایل، کد ملی)<input name="q" value="<?= e($q) ?>"></label>
  <button class="btn">جستجو</button>
  <?php if (staff_can('patients.manage')): ?><a class="btn" href="/admin/patients/new" style="margin-right:auto">بیمار جدید</a><?php endif; ?>
</form>
<p class="hint"><?= fa_num($page['total']) ?> بیمار</p>
<div class="table-wrap"><table>
  <thead><tr><th>نام</th><th>موبایل</th><th>کد ملی</th><th>تاریخ ثبت</th><th></th></tr></thead>
  <tbody><?php foreach ($page['rows'] as $p): ?>
    <tr><td><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td><td class="ltr"><?= e(fa_num($p['mobile'])) ?></td><td><?= e(fa_num($p['national_code'] ?? '—')) ?></td><td><?= jdate($p['created_at']) ?></td>
      <td><a class="btn btn-ghost btn-sm" href="/admin/patients/<?= (int) $p['id'] ?>">پرونده</a></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php partial('admin/_pagination', ['page' => $page]); ?>
