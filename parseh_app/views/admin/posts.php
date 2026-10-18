<div class="toolbar"><a class="btn" href="/admin/posts/new">مقاله جدید</a></div>
<div class="table-wrap"><table>
  <thead><tr><th>عنوان</th><th>وضعیت</th><th></th></tr></thead>
  <tbody><?php foreach ($rows as $p): ?>
    <tr><td><?= e($p['title']) ?></td>
      <td><?= $p['published_at'] ? '<span class="badge badge-ok">منتشر شده ' . jdate($p['published_at']) . '</span>' : '<span class="badge">پیش‌نویس</span>' ?></td>
      <td class="actions"><a class="btn btn-ghost btn-sm" href="/admin/posts/<?= (int) $p['id'] ?>/edit">ویرایش</a>
        <form method="post" action="/admin/posts/<?= (int) $p['id'] ?>/delete" class="inline" onsubmit="return confirm('حذف شود؟')"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">حذف</button></form></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
