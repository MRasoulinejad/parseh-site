<div class="stats">
  <div class="stat"><b><?= fa_num($stats['today']) ?></b><span>نوبت امروز</span></div>
  <div class="stat"><b><?= fa_num($stats['waiting']) ?></b><span>هنوز پذیرش نشده‌اند</span></div>
  <div class="stat"><b><?= fa_num($stats['results']) ?></b><span>جواب در انتظار بارگذاری</span></div>
  <div class="stat"><b><?= fa_num($stats['week']) ?></b><span>نوبت ۷ روز آینده</span></div>
</div>

<div class="box">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
    <h2 style="margin:0">نوبت‌های امروز</h2>
    <?php if (staff_can('appointments.manage')): ?><a class="btn btn-sm" href="/admin/appointments/new">ثبت نوبت حضوری یا تلفنی</a><?php endif; ?>
  </div>
  <?php partial('admin/_appointments_table', ['rows' => $todayList]); ?>
</div>
