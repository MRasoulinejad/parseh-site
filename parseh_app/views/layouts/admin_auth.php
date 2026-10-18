<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($title ?? 'ورود') ?> | پنل رادیولوژی پارسه</title>
<link rel="stylesheet" href="<?= asset('css/site.css') ?>?v=1">
</head>
<body style="background:var(--night)">
<main class="wrap center-box" style="margin-top:10vh">
  <div style="display:flex;justify-content:center;margin-bottom:18px"><?= icon('logo', 56) ?></div>
  <?php foreach (take_flashes() as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
  <?php endforeach; ?>
  <?= $content ?>
</main>
</body>
</html>
