<?php
$me = staff();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH);
$on = fn (string $prefix) => ($prefix === '/admin' ? $path === '/admin' : str_starts_with($path, $prefix)) ? 'on' : '';
$unread = staff_can('messages.reply') ? (int) db_value('SELECT COUNT(*) FROM contact_messages WHERE replied_at IS NULL') : 0;
$pendingResults = staff_can('results.manage') ? (int) db_value("SELECT COUNT(*) FROM appointments a LEFT JOIN results r ON r.appointment_id = a.id WHERE a.status = 'done' AND (r.id IS NULL OR r.status = 'draft')") : 0;
?><!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($title ?? 'پنل مدیریت') ?> | پنل رادیولوژی پارسه</title>
<link rel="stylesheet" href="<?= asset('css/site.css') ?>?v=1">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>?v=1">
</head>
<body class="admin">
<div class="admin-shell">
  <aside class="sidebar">
    <a class="brand" href="/admin"><?= icon('logo', 36) ?><span><strong style="font-size:17px">پنل پارسه</strong><span><?= e($me['role_name']) ?></span></span></a>
    <?php if (staff_can('dashboard.view')): ?><a class="<?= $on('/admin') ?>" href="/admin">داشبورد</a><?php endif; ?>
    <?php if (staff_can('appointments.view')): ?><a class="<?= $on('/admin/appointments') ?>" href="/admin/appointments">نوبت‌ها</a><?php endif; ?>
    <?php if (staff_can('patients.view')): ?><a class="<?= $on('/admin/patients') ?>" href="/admin/patients">بیماران</a><?php endif; ?>
    <?php if (staff_can('messages.reply')): ?><a class="<?= $on('/admin/messages') ?>" href="/admin/messages">پیام‌های تماس <?php if ($unread): ?><span class="count"><?= fa_num($unread) ?></span><?php endif; ?></a><?php endif; ?>
    <?php if (staff_can('results.manage')): ?><a class="<?= $on('/admin/results') ?>" href="/admin/results">جواب‌ها <?php if ($pendingResults): ?><span class="count"><?= fa_num($pendingResults) ?></span><?php endif; ?></a><?php endif; ?>
    <?php if (staff_can('services.manage')): ?>
      <span class="group">خدمات</span>
      <a class="<?= $on('/admin/services') ?>" href="/admin/services">خدمات و دسته‌ها</a>
      <a class="<?= $on('/admin/schedules') ?>" href="/admin/schedules">برنامه کاری و تعطیلات</a>
    <?php endif; ?>
    <?php if (staff_can('content.manage')): ?>
      <span class="group">محتوای سایت</span>
      <a class="<?= $on('/admin/gallery') ?>" href="/admin/gallery">گالری تصاویر</a>
      <a class="<?= $on('/admin/posts') ?>" href="/admin/posts">مقالات</a>
      <a class="<?= $on('/admin/insurances') ?>" href="/admin/insurances">بیمه‌ها</a>
      <a class="<?= $on('/admin/settings') ?>" href="/admin/settings">تنظیمات سایت</a>
    <?php endif; ?>
    <?php if (staff_can('users.manage')): ?>
      <span class="group">کاربران</span>
      <a class="<?= $on('/admin/users') ?>" href="/admin/users">پرسنل</a>
      <a class="<?= $on('/admin/roles') ?>" href="/admin/roles">گروه‌های کاربری</a>
    <?php endif; ?>
    <?php if (staff_can('logs.view')): ?>
      <span class="group">گزارش‌ها</span>
      <a class="<?= $on('/admin/logs') ?>" href="/admin/logs">فعالیت‌ها</a>
      <a class="<?= $on('/admin/sms') ?>" href="/admin/sms">پیامک‌ها</a>
    <?php endif; ?>
    <span class="group">حساب من</span>
    <a class="<?= $on('/admin/password') ?>" href="/admin/password">تغییر رمز</a>
    <a href="/" target="_blank" rel="noopener">مشاهده سایت</a>
  </aside>

  <main class="admin-main">
    <div class="topbar">
      <h1><?= e($title ?? '') ?></h1>
      <div class="who">
        <span><?= e($me['name']) ?></span>
        <form method="post" action="/admin/logout" class="inline"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">خروج</button></form>
      </div>
    </div>
    <?php foreach (take_flashes() as $f): ?>
      <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
  </main>
</div>
</body>
</html>
