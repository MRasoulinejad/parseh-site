<section class="wrap section" style="display:flex;flex-wrap:wrap;gap:40px">
  <div style="flex:1 1 320px;display:flex;flex-direction:column;gap:14px">
    <h1 style="font-size:36px;font-weight:800">تماس با ما</h1>
    <?php if (setting('phone')): ?><p style="margin:0">تلفن پذیرش: <a class="ltr" href="tel:<?= e(setting('phone')) ?>"><?= e(fa_num(setting('phone'))) ?></a></p><?php endif; ?>
    <?php if (setting('address')): ?><p style="margin:0">آدرس: <?= e(setting('address')) ?></p><?php endif; ?>
    <?php if (setting('hours')): ?><p style="margin:0">ساعات کاری: <?= e(setting('hours')) ?></p><?php endif; ?>
    <?php if (setting('map_url')): ?><div class="map" style="min-height:260px"><iframe src="<?= e(setting('map_url')) ?>" loading="lazy" title="نقشه کلینیک" referrerpolicy="no-referrer" style="min-height:260px"></iframe></div><?php endif; ?>
  </div>
  <form method="post" action="/contact" class="panel form" style="flex:1 1 420px">
    <?= csrf_field() ?>
    <h2 style="font-size:22px;font-weight:800">ارسال پیام</h2>
    <label class="field">نام و نام خانوادگی<input name="name" value="<?= e(old('name')) ?>" required maxlength="100"></label>
    <label class="field">شماره موبایل (اختیاری)<input name="mobile" value="<?= e(old('mobile')) ?>" inputmode="tel" maxlength="11" class="ltr"></label>
    <label class="field">پیام<textarea name="message" required maxlength="3000"><?= e(old('message')) ?></textarea></label>
    <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
    <p class="hint" style="margin:0">لطفاً اطلاعات پزشکی یا تصویر جواب را از این فرم نفرستید.</p>
    <button class="btn">ارسال پیام</button>
  </form>
</section>
