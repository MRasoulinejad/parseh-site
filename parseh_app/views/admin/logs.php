<?php
$actions = ['login' => 'ورود', 'logout' => 'خروج', 'view' => 'مشاهده', 'create' => 'ایجاد', 'update' => 'ویرایش', 'save' => 'ذخیره', 'delete' => 'حذف', 'book' => 'رزرو آنلاین', 'cancel' => 'لغو توسط بیمار',
    'view_result' => 'مشاهده جواب', 'upload_result' => 'بارگذاری جواب', 'publish_result' => 'انتشار جواب', 'unpublish_result' => 'لغو انتشار جواب', 'delete_report' => 'حذف گزارش', 'delete_image' => 'حذف تصویر',
    'change_password' => 'تغییر رمز', 'setup' => 'راه‌اندازی', 'add' => 'افزودن'];
$entities = ['appointments' => 'نوبت', 'patients' => 'بیمار', 'results' => 'جواب', 'users' => 'کاربر', 'roles' => 'گروه', 'services' => 'خدمت', 'schedules' => 'برنامه کاری', 'settings' => 'تنظیمات'];
?>
<div class="table-wrap"><table>
  <thead><tr><th>زمان</th><th>چه کسی</th><th>عمل</th><th>روی</th><th>IP</th></tr></thead>
  <tbody><?php foreach ($page['rows'] as $l):
      $who = $l['user_name'] ? e($l['user_name']) : ($l['first_name'] ? 'بیمار: ' . e($l['first_name'] . ' ' . $l['last_name']) : 'بازدیدکننده (کد پیگیری)');
      $act = $actions[$l['action']] ?? (str_starts_with($l['action'], 'status_') ? 'تغییر وضعیت به ' . status_label(substr($l['action'], 7)) : $l['action']); ?>
    <tr><td><?= jdate($l['created_at'], 'datetime') ?></td><td><?= $who ?></td><td><?= e($act) ?></td>
      <td><?= e($entities[$l['entity']] ?? $l['entity']) ?><?php if ($l['entity_id']): ?> #<?= fa_num($l['entity_id']) ?><?php endif; ?></td>
      <td class="ltr"><?= e($l['ip']) ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?php partial('admin/_pagination', ['page' => $page]); ?>
