<section class="wrap section">
  <div style="display:flex;flex-wrap:wrap;gap:40px;align-items:flex-start">
    <article style="flex:999 1 560px;min-width:0">
      <a href="/services#<?= e($service['category_slug']) ?>" class="eyebrow" style="text-decoration:none"><?= e($service['category_title']) ?></a>
      <h1 style="font-size:36px;font-weight:800;margin:6px 0 20px"><?= e($service['title']) ?></h1>
      <div class="prose"><?= paragraphs($service['body']) ?></div>

      <?php if ($gallery): ?>
        <h2 style="font-size:22px;font-weight:800;margin:32px 0 14px">نمونه تصاویر این مرکز</h2>
        <div class="gallery">
          <?php foreach ($gallery as $g): ?>
            <figure><img src="<?= e(public_upload_url($g['file_path'])) ?>" alt="<?= e($g['caption'] ?: $service['title']) ?>" loading="lazy"><?php if ($g['caption']): ?><figcaption><?= e($g['caption']) ?></figcaption><?php endif; ?></figure>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </article>

    <aside style="flex:1 1 300px;display:flex;flex-direction:column;gap:16px;position:sticky;top:100px">
      <?php if ($service['prep_text']): ?>
        <div class="alert alert-info" style="margin:0"><b>آمادگی: </b><?= e($service['prep_text']) ?></div>
      <?php endif; ?>
      <div class="panel" style="padding:24px;display:flex;flex-direction:column;gap:12px">
        <?php if ((int) $service['is_bookable'] === 1): ?>
          <b style="font-size:18px">رزرو آنلاین این خدمت</b>
          <a class="btn btn-block" href="/booking/<?= e($service['slug']) ?>">انتخاب روز و ساعت</a>
        <?php else: ?>
          <b style="font-size:18px">رزرو تلفنی</b>
          <p class="muted" style="margin:0">این خدمت نیاز به هماهنگی قبلی دارد. با پذیرش تماس بگیرید.</p>
          <?php if (setting('phone')): ?><a class="btn btn-block ltr" href="tel:<?= e(setting('phone')) ?>"><?= e(fa_num(setting('phone'))) ?></a><?php endif; ?>
        <?php endif; ?>
      </div>
      <?php if ($related): ?>
        <div class="panel" style="padding:24px">
          <b>خدمات مرتبط</b>
          <ul style="margin:8px 0 0;padding-right:18px">
            <?php foreach ($related as $r): ?><li><a href="/services/<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</section>
