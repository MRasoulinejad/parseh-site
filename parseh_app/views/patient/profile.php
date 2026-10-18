<section class="wrap center-box" style="max-width:620px">
  <form method="post" action="/profile/new" class="panel form">
    <?= csrf_field() ?>
    <h1 style="font-size:26px;font-weight:800">تکمیل مشخصات</h1>
    <p class="muted" style="margin:0">این اطلاعات فقط یک بار پرسیده می‌شود و روی گزارش شما می‌آید.</p>
    <div class="form-row">
      <label class="field">نام<input name="first_name" value="<?= e(old('first_name')) ?>" required maxlength="60"></label>
      <label class="field">نام خانوادگی<input name="last_name" value="<?= e(old('last_name')) ?>" required maxlength="80"></label>
    </div>
    <div class="form-row">
      <label class="field">کد ملی<input name="national_code" value="<?= e(old('national_code')) ?>" inputmode="numeric" maxlength="10" required></label>
      <label class="field">تاریخ تولد (اختیاری)<input name="birth_date" value="<?= e(old('birth_date')) ?>" placeholder="۱۳۷۰/۰۵/۲۱" maxlength="10"></label>
    </div>
    <div class="form-row">
      <label class="field">جنسیت<select name="gender"><option value="">—</option><option value="f" <?= old('gender') === 'f' ? 'selected' : '' ?>>زن</option><option value="m" <?= old('gender') === 'm' ? 'selected' : '' ?>>مرد</option></select></label>
      <label class="field">بیمه<select name="insurance_id"><option value="">آزاد</option><?php foreach ($insurances as $i): ?><option value="<?= (int) $i['id'] ?>" <?= (string) old('insurance_id') === (string) $i['id'] ? 'selected' : '' ?>><?= e($i['name']) ?></option><?php endforeach; ?></select></label>
    </div>
    <button class="btn">ثبت و ادامه</button>
  </form>
</section>
