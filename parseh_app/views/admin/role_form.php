<form method="post" class="box form" style="max-width:860px">
  <?= csrf_field() ?>
  <label class="field">نام گروه<input name="name" value="<?= e($role['name'] ?? '') ?>" required maxlength="100"></label>
  <?php if ($role && (int) $role['is_full_access'] === 1): ?>
    <p class="alert alert-info" style="margin:0">این گروه همیشه به همه بخش‌ها دسترسی کامل دارد.</p>
  <?php else: ?>
    <b>دسترسی‌ها</b>
    <div class="perm-list">
      <?php foreach (all_permissions() as $key => $label): ?>
        <label><input type="checkbox" name="perms[]" value="<?= e($key) ?>" <?= in_array($key, $perms, true) ? 'checked' : '' ?>> <?= e($label) ?></label>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <button class="btn" style="align-self:flex-start">ذخیره</button>
</form>
