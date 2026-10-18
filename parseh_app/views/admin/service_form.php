<form method="post" class="box form" style="max-width:860px">
  <?= csrf_field() ?>
  <div class="form-row">
    <label class="field" style="flex-basis:340px">عنوان<input name="title" value="<?= e($s['title'] ?? '') ?>" required maxlength="150"></label>
    <label class="field">دسته<select name="category_id"><?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) ($s['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option><?php endforeach; ?></select></label>
  </div>
  <div class="form-row">
    <label class="field">نشانی صفحه (انگلیسی)<input name="slug" value="<?= e($s['slug'] ?? '') ?>" class="ltr" placeholder="nt-scan"></label>
    <label class="field">مدت هر نوبت (دقیقه)<input name="duration_min" type="number" min="5" max="240" value="<?= (int) ($s['duration_min'] ?? 15) ?>"></label>
    <label class="field">ترتیب نمایش<input name="sort_order" type="number" value="<?= (int) ($s['sort_order'] ?? 0) ?>"></label>
  </div>
  <label class="field">خلاصه (روی کارت‌ها)<input name="summary" value="<?= e($s['summary'] ?? '') ?>" maxlength="300"></label>
  <label class="field">آمادگی قبل از مراجعه<textarea name="prep_text" style="min-height:70px"><?= e($s['prep_text'] ?? '') ?></textarea></label>
  <label class="field">توضیح کامل<textarea name="body" style="min-height:280px"><?= e(str_replace('\n', "\n", (string) ($s['body'] ?? ''))) ?></textarea></label>
  <p class="hint" style="margin:0">بین پاراگراف‌ها یک خط خالی بگذارید.</p>
  <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="is_bookable" value="1" <?= ($s['is_bookable'] ?? 1) ? 'checked' : '' ?> style="width:auto"> قابل رزرو آنلاین</label>
  <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="is_active" value="1" <?= ($s['is_active'] ?? 1) ? 'checked' : '' ?> style="width:auto"> نمایش در سایت</label>
  <button class="btn" style="align-self:flex-start">ذخیره</button>
</form>
