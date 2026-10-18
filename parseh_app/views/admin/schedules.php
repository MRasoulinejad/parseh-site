<div class="box">
  <h2>ساعات کاری هفتگی</h2>
  <p class="hint" style="margin-top:-6px">بازه «همه خدمات» برای خدماتی است که بازه اختصاصی ندارند. ظرفیت یعنی چند بیمار هم‌زمان در هر نوبت.</p>
  <?php if (!$schedules): ?><p class="muted">هنوز بازه‌ای تعریف نشده؛ تا وقتی تعریف نشود، رزرو آنلاین ممکن نیست.</p><?php else: ?>
  <div class="table-wrap"><table>
    <thead><tr><th>روز</th><th>از</th><th>تا</th><th>خدمت</th><th>ظرفیت</th><th></th></tr></thead>
    <tbody><?php foreach ($schedules as $sc): ?>
      <tr><td><?= e(fa_weekday_name((int) $sc['weekday'])) ?></td><td><?= fa_num(substr($sc['start_time'], 0, 5)) ?></td><td><?= fa_num(substr($sc['end_time'], 0, 5)) ?></td>
        <td><?= e($sc['service_title'] ?? 'همه خدمات') ?></td><td><?= fa_num($sc['capacity']) ?></td>
        <td><form method="post" action="/admin/schedules/<?= (int) $sc['id'] ?>/delete" onsubmit="return confirm('این بازه حذف شود؟')"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">حذف</button></form></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
  <?php endif; ?>

  <form method="post" action="/admin/schedules" class="form" style="margin-top:18px">
    <?= csrf_field() ?>
    <b>افزودن بازه</b>
    <div class="choices">
      <?php for ($d = 0; $d < 7; $d++): ?><label class="choice" style="flex-direction:row;gap:6px"><input type="checkbox" name="weekdays[]" value="<?= $d ?>" style="width:auto"><?= e(fa_weekday_name($d)) ?></label><?php endfor; ?>
    </div>
    <div class="form-row">
      <label class="field">از ساعت<input type="time" name="start_time" required class="ltr"></label>
      <label class="field">تا ساعت<input type="time" name="end_time" required class="ltr"></label>
      <label class="field">خدمت<select name="service_id"><option value="">همه خدمات</option><?php foreach ($services as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['title']) ?></option><?php endforeach; ?></select></label>
      <label class="field">ظرفیت هر نوبت<input type="number" name="capacity" value="1" min="1" max="20"></label>
    </div>
    <button class="btn" style="align-self:flex-start">افزودن</button>
  </form>
</div>

<div class="box">
  <h2>تعطیلات و روزهای استثنا</h2>
  <?php if ($exceptions): ?>
  <div class="table-wrap"><table>
    <thead><tr><th>تاریخ</th><th>وضعیت</th><th>توضیح</th><th></th></tr></thead>
    <tbody><?php foreach ($exceptions as $ex): ?>
      <tr><td><?= jdate($ex['day'], 'long') ?></td>
        <td><?= (int) $ex['is_closed'] ? '<span class="badge badge-err">تعطیل</span>' : 'فقط ' . fa_num(substr((string) $ex['start_time'], 0, 5)) . ' تا ' . fa_num(substr((string) $ex['end_time'], 0, 5)) ?></td>
        <td><?= e($ex['note']) ?></td>
        <td><form method="post" action="/admin/exceptions/<?= (int) $ex['id'] ?>/delete"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">حذف</button></form></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
  <?php endif; ?>
  <form method="post" action="/admin/exceptions" class="toolbar" style="margin-top:14px">
    <?= csrf_field() ?>
    <label class="field">تاریخ<input name="day" placeholder="۱۴۰۵/۰۸/۱۰" required></label>
    <label class="field" style="flex-direction:row;align-items:center;gap:6px;flex-basis:auto"><input type="checkbox" name="is_closed" value="1" checked style="width:auto"> کل روز تعطیل</label>
    <label class="field">یا فقط از<input type="time" name="start_time" class="ltr"></label>
    <label class="field">تا<input type="time" name="end_time" class="ltr"></label>
    <label class="field">توضیح<input name="note" maxlength="200"></label>
    <button class="btn">ثبت</button>
  </form>
</div>
