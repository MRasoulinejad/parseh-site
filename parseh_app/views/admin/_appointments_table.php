<?php if (!$rows): ?>
  <p class="muted" style="margin:12px 0 0">نوبتی نیست.</p>
<?php else: ?>
<div class="table-wrap" style="margin-top:12px">
  <table>
    <thead><tr><th>زمان</th><th>بیمار</th><th>موبایل</th><th>خدمت</th><th>منبع</th><th>وضعیت</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $a):
      $cls = ['cancelled' => 'badge-err', 'no_show' => 'badge-err', 'done' => 'badge-ok', 'checked_in' => 'badge-warn'][$a['status']] ?? ''; ?>
      <tr>
        <td><?= jdate($a['starts_at'], 'datetime') ?></td>
        <td><?= e($a['first_name'] . ' ' . $a['last_name']) ?></td>
        <td class="ltr"><?= e(fa_num($a['mobile'])) ?></td>
        <td><?= e($a['service_title']) ?></td>
        <td><?= e(source_label($a['source'])) ?></td>
        <td><span class="badge <?= $cls ?>"><?= e(status_label($a['status'])) ?></span></td>
        <td><a class="btn btn-ghost btn-sm" href="/admin/appointments/<?= (int) $a['id'] ?>">باز کردن</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
