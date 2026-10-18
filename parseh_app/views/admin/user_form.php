<?php $v = fn ($k, $d = '') => old($k, $u[$k] ?? $d); ?>
<form method="post" class="box form" style="max-width:640px">
  <?= csrf_field() ?>
  <label class="field">نام و نام خانوادگی<input name="name" value="<?= e($v('name')) ?>" required></label>
  <div class="form-row">
    <label class="field">موبایل (نام کاربری)<input name="mobile" value="<?= e($v('mobile')) ?>" required maxlength="11" class="ltr"></label>
    <label class="field">ایمیل (اختیاری)<input name="email" type="email" value="<?= e($v('email')) ?>" class="ltr"></label>
  </div>
  <label class="field">گروه کاربری<select name="role_id" required>
    <?php foreach ($roles as $r): ?><option value="<?= (int) $r['id'] ?>" <?= (string) $v('role_id') === (string) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
  </select></label>
  <label class="field"><?= $u ? 'رمز جدید (اگر نمی‌خواهید تغییر کند خالی بگذارید)' : 'رمز عبور' ?><input type="password" name="password" autocomplete="new-password" <?= $u ? '' : 'required' ?> minlength="10"></label>
  <p class="hint" style="margin:0">حداقل ۱۰ کاراکتر، شامل حرف و عدد.</p>
  <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="is_active" value="1" <?= $v('is_active', 1) ? 'checked' : '' ?> style="width:auto"> حساب فعال است</label>
  <button class="btn" style="align-self:flex-start">ذخیره</button>
</form>
