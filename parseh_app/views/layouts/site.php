<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? '') ? $title . ' | ' . config('app_name') : config('app_name') . ' شیراز') ?></title>
<?php if (!empty($description)): ?><meta name="description" content="<?= e($description) ?>"><?php endif; ?>
<link rel="preload" href="<?= asset('fonts/Vazirmatn-Regular.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= asset('css/site.css') ?>?v=1">
<link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 44"><rect x="2" y="2" width="40" height="40" rx="10" fill="#0B2E33"/><path d="M11 14h22M13 14v17M22 14v17M31 14v17M10 31h24" stroke="#C8A97E" stroke-width="2.4" stroke-linecap="round"/></svg>') ?>">
</head>
<body>
<?php if (config('demo')): ?><div style="background:#8A6A3D;color:#fff;text-align:center;font-size:14px;padding:6px 12px">نسخه نمایشی — اطلاعات واقعی بیمار وارد نکنید. <a href="/admin/login" style="color:#fff">ورود به پنل مدیریت</a></div><?php endif; ?>
<header class="site-header">
  <div class="wrap">
    <a class="brand" href="/">
      <?= icon('logo', 44) ?>
      <span><strong>رادیولوژی پارسه</strong><span>تصویربرداری پزشکی · شیراز</span></span>
    </a>
    <nav class="nav" aria-label="منوی اصلی">
      <a href="/services">خدمات</a>
      <a href="/#doctor">درباره پزشک</a>
      <a href="/#prep">آمادگی‌ها</a>
      <a href="/results">جواب آنلاین</a>
      <a href="/posts">مقالات</a>
      <a href="/contact">تماس</a>
    </nav>
    <div class="header-actions">
      <?php if (patient()): ?>
        <a class="btn btn-ghost btn-sm" href="/panel">پنل من</a>
      <?php endif; ?>
      <a class="btn" href="/booking">نوبت‌دهی آنلاین</a>
    </div>
  </div>
</header>

<main>
  <?php $flashes = take_flashes(); if ($flashes): ?>
    <div class="wrap" style="padding-top:20px">
      <?php foreach ($flashes as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?= $content ?>
</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="col">
      <strong style="font-size:22px">رادیولوژی پارسه</strong>
      <span>زیر نظر دکتر سید محمد جواد هاشمی، متخصص رادیولوژی</span>
    </div>
    <div class="col">
      <strong>تماس</strong>
      <?php if (setting('address')): ?><span><?= e(setting('address')) ?></span><?php endif; ?>
      <?php if (setting('phone')): ?><span>تلفن: <a class="ltr" href="tel:<?= e(setting('phone')) ?>"><?= e(fa_num(setting('phone'))) ?></a></span><?php endif; ?>
      <?php if (setting('hours')): ?><span>ساعات کاری: <?= e(setting('hours')) ?></span><?php endif; ?>
      <?php if (setting('instagram')): ?><span>اینستاگرام: <a class="ltr" href="https://instagram.com/<?= e(ltrim(setting('instagram'), '@')) ?>" rel="noopener" target="_blank"><?= e(setting('instagram')) ?></a></span><?php endif; ?>
    </div>
    <?php if (setting('map_url')): ?>
      <div class="map"><iframe src="<?= e(setting('map_url')) ?>" loading="lazy" title="نقشه کلینیک" referrerpolicy="no-referrer"></iframe></div>
    <?php endif; ?>
  </div>
  <div class="wrap copy">© کلینیک رادیولوژی پارسه — همه حقوق محفوظ است.</div>
</footer>
</body>
</html>
