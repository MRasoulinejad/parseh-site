<form method="post" action="/admin/password" class="box form" style="max-width:480px">
  <?= csrf_field() ?>
  <label class="field">رمز فعلی<input type="password" name="current" required autocomplete="current-password"></label>
  <label class="field">رمز جدید<input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
  <label class="field">تکرار رمز جدید<input type="password" name="password_confirm" required minlength="10" autocomplete="new-password"></label>
  <p class="hint" style="margin:0">حداقل ۱۰ کاراکتر، شامل حرف و عدد.</p>
  <button class="btn">ذخیره</button>
</form>
