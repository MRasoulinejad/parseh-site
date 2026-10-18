<form method="post" class="box form" style="max-width:860px">
  <?= csrf_field() ?>
  <?php foreach (SETTING_FIELDS as $k => $label): $long = in_array($k, ['hero_text', 'doctor_bio', 'hero_title'], true); ?>
    <label class="field"><?= e($label) ?>
      <?php if ($long): ?>
        <textarea name="<?= e($k) ?>" style="min-height:<?= $k === 'hero_title' ? 70 : 120 ?>px"><?= e($values[$k]) ?></textarea>
      <?php else: ?>
        <input name="<?= e($k) ?>" value="<?= e($values[$k]) ?>" <?= in_array($k, ['map_url', 'instagram', 'phone'], true) ? 'class="ltr"' : '' ?>>
      <?php endif; ?>
    </label>
  <?php endforeach; ?>
  <button class="btn" style="align-self:flex-start">ذخیره</button>
</form>
