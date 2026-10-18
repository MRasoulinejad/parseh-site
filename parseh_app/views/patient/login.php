<section class="wrap center-box">
  <form method="post" action="/login" class="panel form">
    <?= csrf_field() ?>
    <h1 style="font-size:26px;font-weight:800">ورود بیماران</h1>
    <p class="muted" style="margin:0">برای رزرو نوبت و دیدن جواب‌ها، شماره موبایل خود را وارد کنید. کد ورود برایتان پیامک می‌شود.</p>
    <label class="field">شماره موبایل<input name="mobile" inputmode="tel" maxlength="11" placeholder="09123456789" class="ltr" required autofocus></label>
    <button class="btn btn-block">ارسال کد</button>
  </form>
</section>
