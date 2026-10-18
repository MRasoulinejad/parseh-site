<div class="box">
  <h2>در انتظار بارگذاری یا انتشار جواب</h2>
  <?php if (!$pending): ?>
    <p class="muted" style="margin:0">همه جواب‌ها منتشر شده‌اند.</p>
  <?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>زمان</th><th>بیمار</th><th>خدمت</th><th>جواب</th><th></th></tr></thead>
      <tbody><?php foreach ($pending as $a): ?>
        <tr><td><?= jdate($a['starts_at'], 'datetime') ?></td><td><?= e($a['first_name'] . ' ' . $a['last_name']) ?></td><td><?= e($a['service_title']) ?></td>
          <td><span class="badge badge-warn"><?= $a['result_status'] ? 'پیش‌نویس' : 'بارگذاری نشده' ?></span></td>
          <td><a class="btn btn-sm" href="/admin/appointments/<?= (int) $a['id'] ?>">بارگذاری جواب</a></td></tr>
      <?php endforeach; ?></tbody>
    </table></div>
  <?php endif; ?>
</div>

<div class="box">
  <h2>آخرین جواب‌های منتشرشده</h2>
  <?php if (!$recent): ?><p class="muted" style="margin:0">هنوز جوابی منتشر نشده است.</p><?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>بیمار</th><th>خدمت</th><th>انتشار</th><th>انقضا</th><th>دیده‌شده</th><th></th></tr></thead>
      <tbody><?php foreach ($recent as $r): ?>
        <tr><td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td><td><?= e($r['service_title']) ?></td><td><?= jdate($r['ready_at']) ?></td><td><?= jdate($r['expires_at']) ?></td>
          <td><?= $r['first_viewed_at'] ? '<span class="badge badge-ok">بله</span>' : '<span class="badge">خیر</span>' ?></td>
          <td><a class="btn btn-ghost btn-sm" href="/admin/appointments/<?= (int) $r['appointment_id'] ?>">باز کردن</a></td></tr>
      <?php endforeach; ?></tbody>
    </table></div>
  <?php endif; ?>
</div>
