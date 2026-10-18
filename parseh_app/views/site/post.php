<article class="wrap section" style="max-width:820px">
  <small class="muted"><?= jdate($post['published_at'], 'long') ?></small>
  <h1 style="font-size:34px;font-weight:800;margin:6px 0 24px"><?= e($post['title']) ?></h1>
  <?php if ($post['cover_path']): ?><img src="<?= e(public_upload_url($post['cover_path'])) ?>" alt="" style="border-radius:16px;margin-bottom:24px;width:100%"><?php endif; ?>
  <div class="prose"><?= paragraphs($post['body']) ?></div>
  <p style="margin-top:32px"><a href="/posts">← همه مقالات</a></p>
</article>
