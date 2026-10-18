<div class="toolbar"><a class="btn" href="/admin/users/new">تعریف پرسنل جدید</a></div>
<div class="table-wrap"><table>
  <thead><tr><th>نام</th><th>موبایل</th><th>گروه</th><th>آخرین ورود</th><th>وضعیت</th><th></th></tr></thead>
  <tbody><?php foreach ($rows as $u): ?>
    <tr><td><?= e($u['name']) ?></td><td class="ltr"><?= e(fa_num($u['mobile'])) ?></td>
      <td><?= e($u['role_name']) ?><?= (int) $u['is_full_access'] ? ' <span class="badge badge-warn">دسترسی کامل</span>' : '' ?></td>
      <td><?= $u['last_login_at'] ? jdate($u['last_login_at'], 'datetime') : '—' ?></td>
      <td><?= (int) $u['is_active'] ? '<span class="badge badge-ok">فعال</span>' : '<span class="badge badge-err">غیرفعال</span>' ?></td>
      <td><?php if (!(int) $u['is_full_access'] || is_full_access()): ?><a class="btn btn-ghost btn-sm" href="/admin/users/<?= (int) $u['id'] ?>/edit">ویرایش</a><?php endif; ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
