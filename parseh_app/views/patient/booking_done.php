<section class="wrap center-box" style="max-width:620px">
  <div class="panel form" style="text-align:center">
    <h1 style="font-size:28px;font-weight:800;color:var(--ok)">نوبت شما ثبت شد</h1>
    <p style="margin:0;font-size:18px"><?= e($a['service_title']) ?></p>
    <p style="margin:0" class="muted"><?= jdate($a['starts_at'], 'long') ?> · ساعت <?= fa_num(jdate($a['starts_at'], 'time')) ?></p>
    <div class="pill-box" style="align-self:center"><small>کد پیگیری</small><b class="ltr" style="font-size:24px;letter-spacing:3px"><?= e($a['tracking_code']) ?></b></div>
    <?php if ($a['prep_text']): ?><div class="alert alert-info" style="text-align:right;margin:0"><b>آمادگی: </b><?= e($a['prep_text']) ?></div><?php endif; ?>
    <p class="hint" style="margin:0">پیامک تأیید و یک روز قبل یادآوری برایتان ارسال می‌شود. ده دقیقه زودتر با نسخه پزشک و کارت ملی مراجعه کنید.</p>
    <a class="btn" href="/panel">رفتن به پنل من</a>
  </div>
</section>
