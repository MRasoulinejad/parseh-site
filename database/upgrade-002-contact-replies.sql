-- فقط برای سایتی که قبلاً با نسخه قبلی نصب شده است (نصب جدید لازم ندارد).
-- اضافه شدن کد پیگیری، ایمیل و پاسخ به پیام‌های فرم تماس.
SET NAMES utf8mb4;

ALTER TABLE contact_messages
  ADD COLUMN tracking_code CHAR(8) NULL AFTER id,
  ADD COLUMN access_token  CHAR(32) NULL AFTER tracking_code,
  ADD COLUMN patient_id    INT UNSIGNED NULL AFTER access_token,
  ADD COLUMN email         VARCHAR(150) NULL AFTER mobile,
  ADD COLUMN reply_text    TEXT NULL AFTER is_read,
  ADD COLUMN replied_by    INT UNSIGNED NULL AFTER reply_text,
  ADD COLUMN replied_at    DATETIME NULL AFTER replied_by,
  ADD COLUMN reply_seen_at DATETIME NULL AFTER replied_at,
  ADD COLUMN email_sent_at DATETIME NULL AFTER reply_seen_at;

UPDATE contact_messages
  SET tracking_code = UPPER(SUBSTRING(MD5(CONCAT(id, RAND())), 1, 8)),
      access_token  = MD5(CONCAT(id, RAND(), NOW())),
      mobile        = COALESCE(mobile, '')
  WHERE tracking_code IS NULL;

ALTER TABLE contact_messages
  MODIFY tracking_code CHAR(8) NOT NULL,
  MODIFY access_token CHAR(32) NOT NULL,
  ADD UNIQUE KEY uq_contact_tracking (tracking_code),
  ADD INDEX idx_contact_mobile (mobile),
  ADD INDEX idx_contact_reply (replied_at);

-- دسترسی جدید «پاسخ به پیام‌ها» برای گروه‌های پیش‌فرض
UPDATE roles SET permissions = REPLACE(permissions, '"results.manage"]', '"results.manage","messages.reply"]') WHERE id = 3 AND permissions NOT LIKE '%messages.reply%';
UPDATE roles SET permissions = REPLACE(permissions, '"content.manage",', '"content.manage","messages.reply",') WHERE id = 2 AND permissions NOT LIKE '%messages.reply%';
