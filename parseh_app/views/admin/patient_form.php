<?php $v = fn ($k) => old($k, $p[$k] ?? ''); ?>
<form method="post" class="box form" style="max-width:720px">
  <?= csrf_field() ?>
  <?php if (!empty($_GET['back'])): ?><input type="hidden" name="back" value="<?= e($_GET['back']) ?>"><?php endif; ?>
  <div class="form-row">
    <label class="field">نام<input name="first_name" value="<?= e($v('first_name')) ?>" required maxlength="60"></label>
    <label class="field">نام خانوادگی<input name="last_name" value="<?= e($v('last_name')) ?>" required maxlength="80"></label>
  </div>
  <div class="form-row">
    <label class="field">موبایل<input name="mobile" value="<?= e($v('mobile')) ?>" required maxlength="11" class="ltr"></label>
    <label class="field">کد ملی<input name="national_code" value="<?= e($v('national_code')) ?>" maxlength="10" inputmode="numeric"></label>
  </div>
  <div class="form-row">
    <label class="field">تاریخ تولد<input name="birth_date" value="<?= e(old('birth_date', !empty($p['birth_date']) ? jdate($p['birth_date'], 'input') : '')) ?>" placeholder="۱۳۷۰/۰۵/۲۱"></label>
    <label class="field">جنسیت<select name="gender"><option value="">—</option><option value="f" <?= $v('gender') === 'f' ? 'selected' : '' ?>>زن</option><option value="m" <?= $v('gender') === 'm' ? 'selected' : '' ?>>مرد</option></select></label>
    <label class="field">بیمه<select name="insurance_id"><option value="">آزاد</option><?php foreach ($insurances as $i): ?><option value="<?= (int) $i['id'] ?>" <?= (string) $v('insurance_id') === (string) $i['id'] ? 'selected' : '' ?>><?= e($i['name']) ?></option><?php endforeach; ?></select></label>
  </div>
  <label class="field">یادداشت داخلی<textarea name="notes" maxlength="500" style="min-height:70px"><?= e($v('notes')) ?></textarea></label>
  <p class="hint" style="margin:0">برای دیدن جواب با کد پیگیری، کد ملی لازم است.</p>
  <button class="btn" style="align-self:flex-start">ذخیره</button>
</form>
