<section class="wrap section">
  <div class="section-head"><h1 style="font-size:36px;font-weight:800">مقالات</h1></div>
  <?php if (!$posts): ?>
    <p class="muted">هنوز مقاله‌ای منتشر نشده است.</p>
  <?php endif; ?>
  <div class="grid">
    <?php foreach ($posts as $p): ?>
      <a class="card" href="/posts/<?= e($p['slug']) ?>" style="padding:0;overflow:hidden">
        <?php if ($p['cover_path']): ?><img src="<?= e(public_upload_url($p['cover_path'])) ?>" alt="" style="aspect-ratio:16/9;object-fit:cover;width:100%" loading="lazy"><?php endif; ?>
        <div style="padding:22px;display:flex;flex-direction:column;gap:8px">
          <small class="muted"><?= jdate($p['published_at'], 'long') ?></small>
          <h3><?= e($p['title']) ?></h3>
          <p><?= e(mb_substr(str_replace('\n', ' ', $p['body']), 0, 160)) ?>…</p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
