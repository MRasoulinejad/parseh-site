<div class="panel" style="display:flex;flex-direction:column;gap:14px;border-color:#9CCBC2">
  <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px;align-items:center">
    <div>
      <b style="font-size:19px"><?= e($r['service_title']) ?></b>
      <div class="muted" style="font-size:14px"><?= e($r['first_name'] . ' ' . $r['last_name']) ?> · <?= jdate($r['starts_at'], 'long') ?></div>
    </div>
    <span class="badge badge-ok">جواب آماده است</span>
  </div>
  <div style="display:flex;flex-wrap:wrap;gap:10px">
    <?php if ($r['report_path']): ?>
      <a class="btn" href="/results/<?= (int) $r['id'] ?>/report" target="_blank" rel="noopener">دانلود گزارش (PDF)</a>
    <?php endif; ?>
  </div>
  <?php if ($r['images']): ?>
    <div class="gallery">
      <?php foreach ($r['images'] as $img): ?>
        <figure><a href="/results/<?= (int) $r['id'] ?>/image/<?= (int) $img['id'] ?>" target="_blank" rel="noopener"><img src="/results/<?= (int) $r['id'] ?>/image/<?= (int) $img['id'] ?>" alt="<?= e($img['caption'] ?: 'تصویر جواب') ?>" loading="lazy"></a><?php if ($img['caption']): ?><figcaption><?= e($img['caption']) ?></figcaption><?php endif; ?></figure>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if ($r['expires_at']): ?><p class="hint" style="margin:0">این جواب تا <?= jdate($r['expires_at']) ?> روی سایت در دسترس است؛ نسخه‌ای از آن را ذخیره کنید.</p><?php endif; ?>
</div>
