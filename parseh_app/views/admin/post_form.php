<form method="post" enctype="multipart/form-data" class="box form" style="max-width:860px">
  <?= csrf_field() ?>
  <label class="field">عنوان<input name="title" value="<?= e($post['title'] ?? '') ?>" required maxlength="200"></label>
  <label class="field">نشانی (انگلیسی، اختیاری)<input name="slug" value="<?= e($post['slug'] ?? '') ?>" class="ltr"></label>
  <label class="field">تصویر شاخص<input type="file" name="cover" accept="image/jpeg,image/png,image/webp"></label>
  <label class="field">متن<textarea name="body" style="min-height:360px" required><?= e($post['body'] ?? '') ?></textarea></label>
  <p class="hint" style="margin:0">بین پاراگراف‌ها یک خط خالی بگذارید.</p>
  <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="publish" value="1" <?= !empty($post['published_at']) ? 'checked' : '' ?> style="width:auto"> منتشر شود</label>
  <button class="btn" style="align-self:flex-start">ذخیره</button>
</form>
