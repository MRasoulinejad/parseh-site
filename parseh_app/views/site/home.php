<section class="hero" id="top">
  <div class="wrap">
    <div class="hero-text">
      <span class="chip">کلینیک رادیولوژی و سونوگرافی</span>
      <h1><?= e(str_replace('\n', "\n", setting('hero_title', "تصویربرداری دقیق،\nتشخیص مطمئن"))) ?></h1>
      <p><?= e(setting('hero_text')) ?></p>
      <div class="hero-actions">
        <a class="btn btn-sand" href="/booking">رزرو نوبت</a>
        <a class="btn btn-light" href="/results">دریافت جواب آنلاین</a>
      </div>
    </div>
    <img class="hero-img" src="<?= asset('img/hero-doctor.jpg') ?>" alt="دکتر سید محمد جواد هاشمی در حال بررسی تصویر رادیولوژی" width="1040" height="840" fetchpriority="high">
  </div>
  <div style="background:var(--ground)">
    <div class="wrap facts">
      <div class="fact"><?= icon('clock', 28, '#0F5F59') ?><div><small>ساعات کاری</small><b><?= e(setting('hours', '—')) ?></b></div></div>
      <div class="fact"><?= icon('phone', 28, '#0F5F59') ?><div><small>تلفن پذیرش</small><b class="ltr"><?= e(fa_num(setting('phone', '—'))) ?></b></div></div>
      <div class="fact"><?= icon('pin', 28, '#0F5F59') ?><div><small>آدرس</small><b><?= e(setting('address', '—')) ?></b></div></div>
    </div>
  </div>
</section>

<section class="wrap" id="services" style="padding-bottom:80px">
  <div class="section-head">
    <span class="eyebrow">خدمات ما</span>
    <h2>خدمات سونوگرافی و رادیولوژی</h2>
    <p>برای هر خدمت، توضیح کامل، آمادگی‌های لازم و رزرو نوبت در صفحه همان خدمت در دسترس است.</p>
  </div>
  <div class="grid">
    <?php foreach ($categories as $cat): $first = $cat['services'][0]; ?>
      <a class="card" href="<?= count($cat['services']) === 1 ? '/services/' . e($first['slug']) : '/services#' . e($cat['slug']) ?>">
        <?= icon($cat['slug'], 40, '#0F5F59') ?>
        <h3><?= e($cat['title']) ?></h3>
        <p><?= e(count($cat['services']) === 1 ? $first['summary'] : $first['summary']) ?></p>
        <?php if (count($cat['services']) > 1): ?>
          <div class="tags"><?php foreach ($cat['services'] as $s): ?><span class="tag"><?= e($s['title']) ?></span><?php endforeach; ?></div>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="doctor" id="doctor">
  <div class="wrap">
    <img class="doctor-photo" src="<?= asset('img/hero-doctor.jpg') ?>" alt="دکتر سید محمد جواد هاشمی" loading="lazy">
    <div class="doctor-text">
      <span class="eyebrow">مدیریت فنی و پزشکی</span>
      <h2>دکتر سید محمد جواد هاشمی</h2>
      <p style="margin:0;font-size:18px;font-weight:500;color:var(--accent)">متخصص رادیولوژی</p>
      <?php if (setting('doctor_bio')): ?><div class="prose"><?= paragraphs(setting('doctor_bio')) ?></div><?php endif; ?>
      <div class="grid" style="gap:12px">
        <?php if (setting('doctor_council_no')): ?><div class="pill-box"><small>شماره نظام پزشکی</small><b><?= e(fa_num(setting('doctor_council_no'))) ?></b></div><?php endif; ?>
        <?php if (setting('doctor_years')): ?><div class="pill-box"><small>سابقه فعالیت</small><b><?= e(fa_num(setting('doctor_years'))) ?> سال</b></div><?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php if ($preps): ?>
<section class="wrap section" id="prep">
  <div class="section-head">
    <span class="eyebrow">پیش از مراجعه بخوانید</span>
    <h2>آمادگی‌های لازم</h2>
  </div>
  <div class="grid">
    <?php foreach ($preps as $p): ?>
      <a class="card" style="flex-basis:260px" href="/services/<?= e($p['slug']) ?>">
        <h3 style="font-size:19px"><?= e($p['title']) ?></h3>
        <p style="color:var(--ink-2)"><?= e($p['prep_text']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
  <p class="muted" style="margin-top:20px;font-size:14px">همراه داشتن نسخه پزشک، کارت ملی و تصاویر و گزارش‌های قبلی الزامی است.</p>
</section>
<?php endif; ?>

<section class="steps">
  <div class="wrap section" style="display:flex;flex-direction:column;gap:36px;padding-top:72px;padding-bottom:72px">
    <h2 style="font-size:32px;font-weight:800">مراحل پذیرش در سه قدم</h2>
    <div class="grid">
      <div><span class="step-num">۱</span><div><h3 style="font-size:19px">رزرو آنلاین نوبت</h3><p>نوع خدمت، روز و ساعت را انتخاب کنید و پیامک تأیید بگیرید.</p></div></div>
      <div><span class="step-num">۲</span><div><h3 style="font-size:19px">مراجعه به کلینیک</h3><p>ده دقیقه زودتر با مدارک و رعایت آمادگی‌ها مراجعه کنید.</p></div></div>
      <div><span class="step-num">۳</span><div><h3 style="font-size:19px">دریافت جواب آنلاین</h3><p>گزارش و تصاویر را با کد پیگیری از همین سایت دریافت کنید.</p></div></div>
    </div>
  </div>
</section>

<section class="wrap section">
  <div class="panel" style="display:flex;flex-wrap:wrap;gap:40px;align-items:center">
    <div style="flex:1 1 360px;min-width:0">
      <h2 style="font-size:30px;font-weight:800">دریافت جواب تصویربرداری</h2>
      <p class="muted" style="margin:8px 0 0">کد پیگیری روی پیامک یا قبض پذیرش و کد ملی خود را وارد کنید.</p>
    </div>
    <form method="post" action="/results" class="form-row" style="flex:1 1 420px;min-width:0;align-items:flex-end">
      <?= csrf_field() ?>
      <label class="field">کد ملی<input name="national_code" inputmode="numeric" maxlength="10" required></label>
      <label class="field">کد پیگیری<input name="tracking_code" maxlength="8" class="ltr" required></label>
      <button class="btn" style="flex:0 0 auto">مشاهده جواب</button>
    </form>
  </div>
</section>

<?php if ($insurances): ?>
<section class="wrap" style="padding-bottom:80px">
  <h2 style="font-size:26px;font-weight:800;margin-bottom:20px">بیمه‌های طرف قرارداد</h2>
  <div class="grid" style="gap:14px">
    <?php foreach ($insurances as $ins): ?>
      <div class="card" style="flex:1 1 160px;align-items:center;justify-content:center;padding:16px;min-height:80px">
        <?php if ($ins['logo_path']): ?><img src="<?= e(public_upload_url($ins['logo_path'])) ?>" alt="<?= e($ins['name']) ?>" style="max-height:48px;width:auto" loading="lazy"><?php else: ?><b><?= e($ins['name']) ?></b><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
