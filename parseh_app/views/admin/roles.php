<div class="toolbar"><a class="btn" href="/admin/roles/new">گروه جدید</a></div>
<div class="table-wrap"><table>
  <thead><tr><th>گروه</th><th>دسترسی‌ها</th><th>تعداد کاربر</th><th></th></tr></thead>
  <tbody><?php foreach ($rows as $r): $perms = json_decode($r['permissions'], true) ?: []; ?>
    <tr><td><b><?= e($r['name']) ?></b></td>
      <td><?php if ((int) $r['is_full_access']): ?><span class="badge badge-warn">دسترسی کامل به همه بخش‌ها</span><?php else: ?><div class="tags"><?php foreach ($perms as $p): ?><span class="tag"><?= e(all_permissions()[$p] ?? $p) ?></span><?php endforeach; ?></div><?php endif; ?></td>
      <td><?= fa_num($r['n']) ?></td>
      <td class="actions">
        <?php if (!(int) $r['is_full_access']): ?><a class="btn btn-ghost btn-sm" href="/admin/roles/<?= (int) $r['id'] ?>/edit">ویرایش</a><?php elseif (is_full_access()): ?><a class="btn btn-ghost btn-sm" href="/admin/roles/<?= (int) $r['id'] ?>/edit">تغییر نام</a><?php endif; ?>
        <?php if (!(int) $r['is_system'] && !(int) $r['is_full_access'] && !(int) $r['n']): ?><form method="post" action="/admin/roles/<?= (int) $r['id'] ?>/delete" class="inline" onsubmit="return confirm('حذف شود؟')"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">حذف</button></form><?php endif; ?>
      </td></tr>
  <?php endforeach; ?></tbody>
</table></div>
