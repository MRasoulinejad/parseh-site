<section class="wrap section">
  <div class="section-head">
    <span class="eyebrow">خدمات ما</span>
    <h1 style="font-size:36px;font-weight:800">خدمات سونوگرافی و رادیولوژی</h1>
  </div>
  <?php foreach ($categories as $cat): ?>
    <div id="<?= e($cat['slug']) ?>" style="margin-bottom:44px">
      <h2 style="font-size:24px;font-weight:800;margin-bottom:16px;display:flex;gap:10px;align-items:center"><?= icon($cat['slug'], 28, '#0F5F59') ?><?= e($cat['title']) ?></h2>
      <div class="grid">
        <?php foreach ($cat['services'] as $s): ?>
          <a class="card" href="/services/<?= e($s['slug']) ?>">
            <h3><?= e($s['title']) ?></h3>
            <p><?= e($s['summary']) ?></p>
            <?php if ($s['prep_text']): ?><p class="note">نیاز به آمادگی دارد</p><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</section>
