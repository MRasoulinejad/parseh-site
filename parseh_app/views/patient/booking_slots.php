<section class="wrap section" style="max-width:900px">
  <a href="/booking" class="hint">← تغییر خدمت</a>
  <h1 style="font-size:30px;font-weight:800;margin:6px 0 20px">رزرو <?= e($service['title']) ?></h1>

  <?php if ($service['prep_text']): ?>
    <div class="alert alert-info"><b>آمادگی: </b><?= e($service['prep_text']) ?></div>
  <?php endif; ?>

  <?php if (!$days): ?>
    <div class="panel"><p style="margin:0">در روزهای پیش رو نوبت خالی برای این خدمت نیست. لطفاً با پذیرش تماس بگیرید<?= setting('phone') ? ': <span class="ltr">' . e(fa_num(setting('phone'))) . '</span>' : '' ?>.</p></div>
  <?php else: ?>
    <div class="panel form">
      <h2 style="font-size:18px;font-weight:800">مرحله ۲: روز مراجعه</h2>
      <div class="choices">
        <?php foreach ($days as $d): ?>
          <a class="choice <?= $d['date'] === $selected ? 'on' : '' ?>" href="?date=<?= e($d['date']) ?>">
            <span><?= e(fa_weekday_name(fa_weekday_index($d['date']))) ?></span><b><?= jdate($d['date'], 'day') ?></b>
          </a>
        <?php endforeach; ?>
      </div>

      <h2 style="font-size:18px;font-weight:800;margin-top:12px">مرحله ۳: ساعت (<?= jdate($selected, 'long') ?>)</h2>
      <?php if (!$slots): ?>
        <p class="muted">ساعت خالی برای این روز نمانده است.</p>
      <?php else: ?>
        <form method="post" action="/booking" class="form">
          <?= csrf_field() ?>
          <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">
          <div class="choices" role="radiogroup" aria-label="ساعت">
            <?php foreach ($slots as $i => $s): ?>
              <label class="choice" style="cursor:pointer">
                <input type="radio" name="starts_at" value="<?= e($s['starts_at']) ?>" <?= $i === 0 ? 'checked' : '' ?> style="width:auto;margin:0 0 4px">
                <b><?= e(fa_num($s['time'])) ?></b>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if ($insurances): ?>
            <label class="field" style="max-width:320px">بیمه<select name="insurance_id"><option value="">بیمه ثبت‌شده در پرونده / آزاد</option><?php foreach ($insurances as $ins): ?><option value="<?= (int) $ins['id'] ?>"><?= e($ins['name']) ?></option><?php endforeach; ?></select></label>
          <?php endif; ?>
          <?php if (patient()): ?>
            <button class="btn">تأیید و ثبت نوبت</button>
          <?php else: ?>
            <p class="hint" style="margin:0">برای ثبت نوبت ابتدا با شماره موبایل وارد شوید.</p>
            <a class="btn" href="/login?next=<?= e(rawurlencode($_SERVER['REQUEST_URI'] ?? '/booking')) ?>">ورود و ثبت نوبت</a>
          <?php endif; ?>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
