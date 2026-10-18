<div class="toolbar">
  <a class="choice <?= $filter === 'open' ? 'on' : '' ?>" href="/admin/messages">بی‌پاسخ (<?= fa_num($open) ?>)</a>
  <a class="choice <?= $filter === 'all' ? 'on' : '' ?>" href="/admin/messages?f=all">همه پیام‌ها</a>
</div>
<?php if (!$page['rows']): ?><p class="muted"><?= $filter === 'open' ? 'پیام بی‌پاسخی نیست.' : 'پیامی نیست.' ?></p><?php endif; ?>
<?php foreach ($page['rows'] as $m): ?>
  <div class="box" id="m<?= (int) $m['id'] ?>" style="<?= $m['replied_at'] ? '' : 'border-color:var(--sand)' ?>">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;align-items:center">
      <div>
        <b><?= e($m['name']) ?></b>
        <a class="ltr" href="tel:<?= e($m['mobile']) ?>" style="margin-right:8px"><?= e(fa_num($m['mobile'])) ?></a>
        <?php if ($m['email']): ?><span class="ltr muted" style="margin-right:8px;font-size:14px"><?= e($m['email']) ?></span><?php endif; ?>
      </div>
      <div class="muted" style="font-size:14px">
        <span class="ltr"><?= e($m['tracking_code']) ?></span> · <?= jdate($m['created_at'], 'datetime') ?>
      </div>
    </div>
    <p style="margin:10px 0;white-space:pre-line"><?= e($m['message']) ?></p>

    <?php if ($m['replied_at']): ?>
      <div style="background:#F3FAF8;border:1px solid #9CCBC2;border-radius:12px;padding:12px 14px;margin-bottom:10px">
        <div style="font-size:13px;color:var(--ok);font-weight:700">
          پاسخ <?= e($m['replier_name'] ?? '') ?> · <?= jdate($m['replied_at'], 'datetime') ?>
          <?php if ($m['email_sent_at']): ?> · ایمیل ارسال شد<?php endif; ?>
          <?php if ($m['reply_seen_at']): ?> · <span>فرستنده دیده</span><?php endif; ?>
        </div>
        <p style="margin:6px 0 0;white-space:pre-line"><?= e($m['reply_text']) ?></p>
      </div>
    <?php endif; ?>

    <form method="post" action="/admin/messages/<?= (int) $m['id'] ?>/reply" class="form">
      <?= csrf_field() ?>
      <label class="field"><?= $m['replied_at'] ? 'ویرایش پاسخ' : 'پاسخ' ?><textarea name="reply" maxlength="3000" style="min-height:90px" <?= $m['replied_at'] ? '' : 'required' ?>><?= e($m['reply_text'] ?? '') ?></textarea></label>
      <div class="actions" style="align-items:center">
        <button class="btn btn-sm"><?= $m['replied_at'] ? 'ذخیره پاسخ' : 'ارسال پاسخ' ?></button>
        <?php if ($m['replied_at'] && $m['email']): ?>
          <label style="display:flex;gap:6px;align-items:center;font-size:14px"><input type="checkbox" name="notify_again" value="1" style="width:auto"> دوباره ایمیل اطلاع‌رسانی بفرست</label>
        <?php endif; ?>
        <?php if (!$m['email'] && !$m['replied_at']): ?><span class="hint">فرستنده ایمیل نداده؛ پاسخ را با کد پیگیری روی سایت می‌بیند.</span><?php endif; ?>
        <?php if (staff_can('content.manage')): ?>
          <button class="btn btn-ghost btn-sm" formaction="/admin/messages/<?= (int) $m['id'] ?>/delete" formnovalidate onclick="return confirm('این پیام حذف شود؟')" style="margin-right:auto">حذف</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
<?php endforeach; ?>
<?php partial('admin/_pagination', ['page' => $page]); ?>
