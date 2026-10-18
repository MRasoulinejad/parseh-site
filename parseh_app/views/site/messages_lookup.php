<section class="wrap center-box">
  <form method="post" action="/messages" class="panel form">
    <?= csrf_field() ?>
    <h1 style="font-size:26px;font-weight:800">پیگیری پیام</h1>
    <p class="muted" style="margin:0">کد پیگیری‌ای که بعد از ارسال پیام دریافت کردید و شماره موبایلتان را وارد کنید.</p>
    <label class="field">کد پیگیری<input name="code" maxlength="8" class="ltr" required autofocus></label>
    <label class="field">شماره موبایل<input name="mobile" inputmode="tel" maxlength="11" class="ltr" required></label>
    <button class="btn btn-block">مشاهده پاسخ</button>
    <a href="/contact" class="hint" style="text-align:center">ارسال پیام جدید</a>
  </form>
</section>
