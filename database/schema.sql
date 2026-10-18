-- پایگاه داده سایت رادیولوژی پارسه
-- در phpMyAdmin دایرکت‌ادمین: دیتابیس را انتخاب کنید → Import → این فایل، سپس seed.sql
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE roles (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(100) NOT NULL,
  permissions    TEXT NOT NULL,
  is_full_access TINYINT(1) NOT NULL DEFAULT 0,
  is_system      TINYINT(1) NOT NULL DEFAULT 0,
  created_at     DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id       INT UNSIGNED NOT NULL,
  name          VARCHAR(100) NOT NULL,
  mobile        CHAR(11) NOT NULL UNIQUE,
  email         VARCHAR(150) NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at    DATETIME NOT NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE insurances (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  logo_path  VARCHAR(255) NULL,
  is_active  TINYINT(1) NOT NULL DEFAULT 1,
  sort_order SMALLINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE patients (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  national_code CHAR(10) NULL UNIQUE,
  first_name    VARCHAR(60) NOT NULL,
  last_name     VARCHAR(80) NOT NULL,
  mobile        CHAR(11) NOT NULL,
  birth_date    DATE NULL,
  gender        VARCHAR(1) NULL,
  insurance_id  INT UNSIGNED NULL,
  notes         VARCHAR(500) NULL,
  created_at    DATETIME NOT NULL,
  FOREIGN KEY (insurance_id) REFERENCES insurances(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE otp_codes (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mobile     CHAR(11) NOT NULL,
  code_hash  VARCHAR(255) NOT NULL,
  attempts   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  used_at    DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rate_limits (
  `key`    VARCHAR(150) NOT NULL PRIMARY KEY,
  hits     INT UNSIGNED NOT NULL DEFAULT 0,
  reset_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE service_categories (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title      VARCHAR(100) NOT NULL,
  slug       VARCHAR(100) NOT NULL UNIQUE,
  sort_order SMALLINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE services (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id  INT UNSIGNED NOT NULL,
  title        VARCHAR(150) NOT NULL,
  slug         VARCHAR(150) NOT NULL UNIQUE,
  summary      VARCHAR(300) NULL,
  body         MEDIUMTEXT NULL,
  prep_text    TEXT NULL,
  duration_min SMALLINT UNSIGNED NOT NULL DEFAULT 15,
  is_bookable  TINYINT(1) NOT NULL DEFAULT 1,
  is_active    TINYINT(1) NOT NULL DEFAULT 1,
  sort_order   SMALLINT NOT NULL DEFAULT 0,
  FOREIGN KEY (category_id) REFERENCES service_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE schedules (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_id INT UNSIGNED NULL,
  weekday    TINYINT UNSIGNED NOT NULL,
  start_time TIME NOT NULL,
  end_time   TIME NOT NULL,
  capacity   TINYINT UNSIGNED NOT NULL DEFAULT 1,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE schedule_exceptions (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  day        DATE NOT NULL UNIQUE,
  is_closed  TINYINT(1) NOT NULL DEFAULT 1,
  start_time TIME NULL,
  end_time   TIME NULL,
  note       VARCHAR(200) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE appointments (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  patient_id    INT UNSIGNED NOT NULL,
  service_id    INT UNSIGNED NOT NULL,
  starts_at     DATETIME NOT NULL,
  status        VARCHAR(20) NOT NULL DEFAULT 'confirmed',
  tracking_code CHAR(8) NOT NULL UNIQUE,
  insurance_id  INT UNSIGNED NULL,
  source        VARCHAR(10) NOT NULL DEFAULT 'online',
  notes         VARCHAR(500) NULL,
  created_by    INT UNSIGNED NULL,
  reminded_at   DATETIME NULL,
  created_at    DATETIME NOT NULL,
  FOREIGN KEY (patient_id)   REFERENCES patients(id),
  FOREIGN KEY (service_id)   REFERENCES services(id),
  FOREIGN KEY (insurance_id) REFERENCES insurances(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by)   REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE results (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  appointment_id  INT UNSIGNED NOT NULL UNIQUE,
  report_path     VARCHAR(255) NULL,
  status          VARCHAR(10) NOT NULL DEFAULT 'draft',
  uploaded_by     INT UNSIGNED NULL,
  ready_at        DATETIME NULL,
  expires_at      DATETIME NULL,
  first_viewed_at DATETIME NULL,
  created_at      DATETIME NOT NULL,
  FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by)    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE result_images (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  result_id  INT UNSIGNED NOT NULL,
  file_path  VARCHAR(255) NOT NULL,
  caption    VARCHAR(200) NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  FOREIGN KEY (result_id) REFERENCES results(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE gallery_items (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_id INT UNSIGNED NULL,
  file_path  VARCHAR(255) NOT NULL,
  caption    VARCHAR(200) NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE posts (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(200) NOT NULL,
  slug         VARCHAR(200) NOT NULL UNIQUE,
  body         MEDIUMTEXT NOT NULL,
  cover_path   VARCHAR(255) NULL,
  published_at DATETIME NULL,
  created_at   DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE contact_messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  mobile     CHAR(11) NULL,
  message    TEXT NOT NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE sms_logs (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mobile      CHAR(11) NOT NULL,
  template    VARCHAR(50) NOT NULL,
  status      VARCHAR(10) NOT NULL DEFAULT 'queued',
  provider_id VARCHAR(100) NULL,
  created_at  DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
  `key`   VARCHAR(100) NOT NULL PRIMARY KEY,
  `value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE audit_logs (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NULL,
  patient_id INT UNSIGNED NULL,
  action     VARCHAR(50) NOT NULL,
  entity     VARCHAR(50) NOT NULL,
  entity_id  INT UNSIGNED NULL,
  ip         VARCHAR(45) NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_patients_mobile ON patients (mobile);
CREATE INDEX idx_otp_mobile ON otp_codes (mobile, expires_at);
CREATE INDEX idx_schedules_weekday ON schedules (weekday, service_id);
CREATE INDEX idx_appt_time ON appointments (starts_at, service_id);
CREATE INDEX idx_appt_patient ON appointments (patient_id);
CREATE INDEX idx_results_status ON results (status, expires_at);
CREATE INDEX idx_audit_entity ON audit_logs (entity, entity_id);
CREATE INDEX idx_audit_time ON audit_logs (created_at);
CREATE INDEX idx_sms_time ON sms_logs (created_at);

SET FOREIGN_KEY_CHECKS = 1;
