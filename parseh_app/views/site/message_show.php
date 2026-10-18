<section class="wrap section" style="max-width:760px">
  <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:20px">
    <h1 style="font-size:28px;font-weight:800">پیام شما</h1>
    <div class="pill-box"><small>کد پیگیری</small><b class="ltr" style="font-size:20px;letter-spacing:2px"><?= e($m['tracking_code']) ?></b></div>
  </div>

  <div class="panel" style="display:flex;flex-direction:column;gap:6px">
    <div class="muted" style="font-size:14px"><?= e($m['name']) ?> · <?= jdate($m['created_at'], 'datetime') ?></div>
    <p style="margin:0;white-space:pre-line"><?= e($m['message']) ?></p>
  </div>

  <?php if ($m['reply_text']): ?>
    <div class="panel" style="margin-top:16px;border-color:#9CCBC2;background:#F3FAF8;display:flex;flex-direction:column;gap:6px">
      <div style="font-size:14px;color:var(--ok);font-weight:700">پاسخ کلینیک رادیولوژی پارسه · <?= jdate($m['replied_at'], 'datetime') ?></div>
      <p style="margin:0;white-space:pre-line"><?= e($m['reply_text']) ?></p>
    </div>
    <p class="hint" style="margin-top:14px">سؤال دیگری دارید؟ <a href="/contact">پیام جدید بفرستید</a> یا با پذیرش تماس بگیرید<?= setting('phone') ? ': <span class="ltr">' . e(fa_num(setting('phone'))) . '</span>' : '' ?>.</p>
  <?php else: ?>
    <div class="alert alert-info" style="margin-top:16px">پیام شما ثبت شده و هنوز پاسخ داده نشده است.
      <?= $m['email'] ? 'وقتی پاسخ داده شود، به ایمیل ' . e($m['email']) . ' خبر می‌دهیم.' : 'برای دیدن پاسخ، بعداً با همین کد پیگیری و شماره موبایل به صفحه «پیگیری پیام» سر بزنید.' ?>
    </div>
    <p class="hint">کد پیگیری را یادداشت کنید: <b class="ltr"><?= e($m['tracking_code']) ?></b> — صفحه پیگیری: <a href="/messages">/messages</a></p>
  <?php endif; ?>
</section>
