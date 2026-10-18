<section class="wrap section">
  <div class="section-head">
    <span class="eyebrow">مرحله ۱ از ۳</span>
    <h1 style="font-size:34px;font-weight:800">کدام خدمت را می‌خواهید؟</h1>
  </div>
  <?php foreach ($categories as $cat): ?>
    <h2 style="font-size:20px;font-weight:800;margin:28px 0 12px;display:flex;gap:8px;align-items:center"><?= icon($cat['slug'], 24, '#0F5F59') ?><?= e($cat['title']) ?></h2>
    <div class="choices">
      <?php foreach ($cat['services'] as $s): ?>
        <?php if ((int) $s['is_bookable'] === 1): ?>
          <a class="choice" href="/booking/<?= e($s['slug']) ?>"><?= e($s['title']) ?></a>
        <?php else: ?>
          <a class="choice" href="/services/<?= e($s['slug']) ?>" title="نیاز به هماهنگی تلفنی"><?= e($s['title']) ?> <small class="muted">(تلفنی)</small></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</section>
