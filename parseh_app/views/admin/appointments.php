<form method="get" class="toolbar">
  <label class="field">تاریخ<input name="date" value="<?= e(jdate($date, 'input')) ?>" placeholder="۱۴۰۵/۰۸/۰۱"></label>
  <label class="field">وضعیت<select name="status"><option value="">همه</option><?php foreach (APPOINTMENT_STATUSES as $s): ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select></label>
  <label class="field" style="flex-basis:260px">جستجو (نام، موبایل، کد ملی، کد پیگیری)<input name="q" value="<?= e($q) ?>"></label>
  <button class="btn">نمایش</button>
  <a class="btn btn-ghost" href="/admin/appointments?date=<?= e(jdate(date('Y-m-d', strtotime($date . ' -1 day')), 'input')) ?>">روز قبل</a>
  <a class="btn btn-ghost" href="/admin/appointments">امروز</a>
  <a class="btn btn-ghost" href="/admin/appointments?date=<?= e(jdate(date('Y-m-d', strtotime($date . ' +1 day')), 'input')) ?>">روز بعد</a>
  <?php if (staff_can('appointments.manage')): ?><a class="btn" href="/admin/appointments/new" style="margin-right:auto">ثبت نوبت</a><?php endif; ?>
</form>
<?php partial('admin/_appointments_table', ['rows' => $page['rows']]); ?>
<?php partial('admin/_pagination', ['page' => $page]); ?>
