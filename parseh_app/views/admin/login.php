<form method="post" action="/admin/login" class="panel form">
  <?= csrf_field() ?>
  <h1 style="font-size:24px;font-weight:800">ورود پرسنل کلینیک</h1>
  <label class="field">شماره موبایل<input name="mobile" inputmode="tel" maxlength="11" class="ltr" required autofocus autocomplete="username"></label>
  <label class="field">رمز عبور<input type="password" name="password" required autocomplete="current-password"></label>
  <button class="btn btn-block">ورود</button>
  <?php if (config('demo')): ?>
    <div class="alert alert-info" style="margin:0;font-size:14px;line-height:2">
      <b>حساب‌های نمایشی</b><br>
      دکتر هاشمی (دسترسی کامل): <span class="ltr">09170000001</span> / <span class="ltr">Parseh1405demo</span><br>
      مدیر سایت: <span class="ltr">09170000003</span> / <span class="ltr">Manager1405demo</span><br>
      پرسنل پذیرش: <span class="ltr">09170000002</span> / <span class="ltr">Reception1405demo</span>
    </div>
  <?php endif; ?>
  <p class="hint" style="margin:0">رمز را فراموش کرده‌اید؟ از مدیر سایت بخواهید رمز جدید تعریف کند.</p>
</form>
