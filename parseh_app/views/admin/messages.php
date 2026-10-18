<?php if (!$page['rows']): ?><p class="muted">پیامی نیست.</p><?php endif; ?>
<?php foreach ($page['rows'] as $m): ?>
  <div class="box" style="<?= (int) $m['is_read'] ? 'opacity:.75' : 'border-color:var(--sand)' ?>">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px">
      <b><?= e($m['name']) ?> <?php if ($m['mobile']): ?><span class="ltr muted" style="font-weight:400"><?= e(fa_num($m['mobile'])) ?></span><?php endif; ?></b>
      <span class="muted" style="font-size:14px"><?= jdate($m['created_at'], 'datetime') ?></span>
    </div>
    <p style="margin:8px 0;white-space:pre-line"><?= e($m['message']) ?></p>
    <div class="actions">
      <?php if (!(int) $m['is_read']): ?><form method="post" action="/admin/messages/<?= (int) $m['id'] ?>/read" class="inline"><?= csrf_field() ?><button class="btn btn-sm">خوانده شد</button></form><?php endif; ?>
      <form method="post" action="/admin/messages/<?= (int) $m['id'] ?>/delete" class="inline" onsubmit="return confirm('حذف شود؟')"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">حذف</button></form>
    </div>
  </div>
<?php endforeach; ?>
<?php partial('admin/_pagination', ['page' => $page]); ?>
