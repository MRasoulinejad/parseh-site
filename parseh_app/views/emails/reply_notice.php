<!doctype html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><title>پاسخ پیام</title></head>
<body style="margin:0;background:#F5F3EE;font-family:Tahoma,Arial,sans-serif;color:#12262B">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F3EE;padding:24px 0">
    <tr><td align="center">
      <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border:1px solid #E4DED2;border-radius:14px;overflow:hidden" dir="rtl">
        <tr><td style="background:#0B2E33;color:#ffffff;padding:18px 24px;font-size:18px;font-weight:bold;text-align:right">رادیولوژی پارسه</td></tr>
        <tr><td style="padding:24px;text-align:right;font-size:15px;line-height:2">
          <p style="margin:0 0 12px">سلام <?= e($m['name']) ?>،</p>
          <p style="margin:0 0 20px">درخواست شماره <b dir="ltr"><?= e($m['tracking_code']) ?></b> شما پاسخ داده شد. برای مشاهده پاسخ روی دکمه زیر کلیک کنید.</p>
          <p style="margin:0 0 20px;text-align:center">
            <a href="<?= e($link) ?>" style="display:inline-block;background:#0F5F59;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 28px;border-radius:10px">مشاهده پاسخ</a>
          </p>
          <p style="margin:0;font-size:13px;color:#55666A">اگر دکمه کار نکرد، این لینک را در مرورگر باز کنید:<br><span dir="ltr" style="word-break:break-all"><?= e($link) ?></span></p>
        </td></tr>
        <tr><td style="padding:14px 24px;background:#FBFAF7;font-size:12px;color:#55666A;text-align:right">این ایمیل خودکار است؛ لطفاً به آن پاسخ ندهید. برای سؤال جدید از فرم تماس سایت استفاده کنید.</td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
