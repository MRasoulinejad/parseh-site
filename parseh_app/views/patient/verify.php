<section class="wrap center-box">
  <form method="post" action="/login/verify" class="panel form">
    <?= csrf_field() ?>
    <h1 style="font-size:26px;font-weight:800">کد ورود</h1>
    <p class="muted" style="margin:0">کد ۵ رقمی پیامک‌شده به <span class="ltr"><?= e(fa_num($mobile)) ?></span> را وارد کنید. این کد ۲ دقیقه اعتبار دارد.</p>
    <input name="code" inputmode="numeric" maxlength="5" autocomplete="one-time-code" class="otp-input" required autofocus aria-label="کد ورود">
    <button class="btn btn-block">ورود</button>
    <a href="/login" class="hint" style="text-align:center">تغییر شماره یا ارسال دوباره کد</a>
  </form>
</section>
