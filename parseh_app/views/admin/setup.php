<form method="post" action="/admin/setup" class="panel form">
  <?= csrf_field() ?>
  <h1 style="font-size:24px;font-weight:800">راه‌اندازی اولیه</h1>
  <p class="muted" style="margin:0">اولین حساب، حساب «دکتر هاشمی — دسترسی کامل» است. این صفحه بعد از ساخت حساب برای همیشه بسته می‌شود.</p>
  <label class="field">نام<input name="name" value="دکتر سید محمد جواد هاشمی" required></label>
  <label class="field">موبایل (نام کاربری)<input name="mobile" inputmode="tel" maxlength="11" class="ltr" required></label>
  <label class="field">رمز عبور<input type="password" name="password" autocomplete="new-password" required minlength="10"></label>
  <label class="field">تکرار رمز<input type="password" name="password_confirm" autocomplete="new-password" required minlength="10"></label>
  <p class="hint" style="margin:0">حداقل ۱۰ کاراکتر، شامل حرف و عدد.</p>
  <button class="btn btn-block">ساخت حساب</button>
</form>
