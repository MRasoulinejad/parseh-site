<?php if ($page['pages'] > 1): ?>
<nav class="pagination" aria-label="صفحه‌ها">
  <?php for ($i = 1; $i <= $page['pages']; $i++): ?>
    <a class="choice <?= $i === $page['page'] ? 'on' : '' ?>" href="<?= e(page_url($i)) ?>" style="min-width:44px"><?= fa_num($i) ?></a>
  <?php endfor; ?>
</nav>
<?php endif; ?>
