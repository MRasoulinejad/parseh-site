<div class="box">
  <dl class="kv">
    <dt>موبایل</dt><dd class="ltr" style="text-align:right"><?= e(fa_num($p['mobile'])) ?></dd>
    <dt>کد ملی</dt><dd><?= e(fa_num($p['national_code'] ?? '—')) ?></dd>
    <dt>تاریخ تولد</dt><dd><?= $p['birth_date'] ? jdate($p['birth_date']) : '—' ?></dd>
    <dt>جنسیت</dt><dd><?= ['f' => 'زن', 'm' => 'مرد'][$p['gender'] ?? ''] ?? '—' ?></dd>
    <dt>بیمه</dt><dd><?= e($p['insurance_name'] ?? 'آزاد') ?></dd>
    <?php if ($p['notes']): ?><dt>یادداشت</dt><dd><?= e($p['notes']) ?></dd><?php endif; ?>
  </dl>
  <div class="actions" style="margin-top:14px">
    <?php if (staff_can('patients.manage')): ?><a class="btn btn-ghost btn-sm" href="/admin/patients/<?= (int) $p['id'] ?>/edit">ویرایش</a><?php endif; ?>
    <?php if (staff_can('appointments.manage')): ?><a class="btn btn-sm" href="/admin/appointments/new?patient_id=<?= (int) $p['id'] ?>">نوبت جدید برای این بیمار</a><?php endif; ?>
  </div>
</div>
<div class="box">
  <h2>نوبت‌ها</h2>
  <?php partial('admin/_appointments_table', ['rows' => $appointments]); ?>
</div>
