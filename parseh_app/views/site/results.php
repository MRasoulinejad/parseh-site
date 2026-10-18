<section class="wrap section" style="max-width:860px">
  <h1 style="font-size:34px;font-weight:800;margin-bottom:8px">دریافت جواب آنلاین</h1>
  <p class="muted" style="margin:0 0 24px">کد پیگیری در پیامک تأیید نوبت و روی قبض پذیرش آمده است. اگر وارد پنل خود شوید، همه جواب‌هایتان را یکجا می‌بینید.</p>

  <?php if ($result): ?>
    <?php partial('site/_result_card', ['r' => $result]); ?>
  <?php endif; ?>

  <form method="post" action="/results" class="panel form" style="margin-top:20px">
    <?= csrf_field() ?>
    <div class="form-row">
      <label class="field">کد ملی بیمار<input name="national_code" inputmode="numeric" maxlength="10" required></label>
      <label class="field">کد پیگیری<input name="tracking_code" maxlength="8" class="ltr" required></label>
    </div>
    <button class="btn">مشاهده جواب</button>
    <p class="hint" style="margin:0">یا <a href="/login">با شماره موبایل وارد شوید</a>.</p>
  </form>
</section>
