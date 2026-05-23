-- ============================================================
-- BUSINESS AD MANAGEMENT SYSTEM
-- Merges with: tn_ad_slots, tn_districts, tn_cities, tn_categories
-- ============================================================
USE `tamilnews_db`;

-- ── Extend tn_ad_slots: add new positions for masthead ────────
ALTER TABLE `tn_ad_slots`
  MODIFY COLUMN `position`
    ENUM(
      'header',
      'masthead_left', 'masthead_right', 'header_banner',
      'in_article_after_p3', 'in_article_after_p6',
      'sidebar', 'footer',
      'category_top', 'breaking_below'
    ) NOT NULL DEFAULT 'header';

-- Insert new positions if not exist
INSERT IGNORE INTO `tn_ad_slots` (`name`,`slug`,`position`,`desktop_size`,`mobile_size`,`is_active`) VALUES
('Masthead Left',   'masthead-left',   'masthead_left',   '200x80', '',      1),
('Masthead Right',  'masthead-right',  'masthead_right',  '200x80', '',      1),
('Header Banner',   'header-banner',   'header_banner',   '728x60', '320x50',1),
('Category Top',    'category-top',    'category_top',    '728x90', '320x50',1),
('Below Breaking',  'breaking-below',  'breaking_below',  '728x90', '320x50',1);

-- ── BUSINESS ADS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tn_business_ads` (
  `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `business_name`    VARCHAR(200)     NOT NULL,
  `contact_phone`    VARCHAR(20)          NULL DEFAULT NULL,
  `contact_email`    VARCHAR(150)         NULL DEFAULT NULL,

  -- Location targeting
  `district_id`      SMALLINT UNSIGNED    NULL DEFAULT NULL COMMENT 'tn_districts.id',
  `city_id`          SMALLINT UNSIGNED    NULL DEFAULT NULL COMMENT 'tn_cities.id',

  -- Display config
  `slot_id`          TINYINT UNSIGNED NOT NULL COMMENT 'tn_ad_slots.id',
  `display_type`     ENUM('global','location','category') NOT NULL DEFAULT 'global',
  `category_id`      INT UNSIGNED         NULL DEFAULT NULL COMMENT 'for category-based display',

  -- Validity
  `valid_from`       DATE             NOT NULL,
  `valid_until`      DATE             NOT NULL,

  -- Payment
  `payment_status`   ENUM('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `payment_amount`   DECIMAL(10,2)        NULL DEFAULT NULL,
  `payment_note`     VARCHAR(300)         NULL DEFAULT NULL,
  `payment_confirmed_by` INT UNSIGNED     NULL DEFAULT NULL,
  `payment_confirmed_at` DATETIME         NULL DEFAULT NULL,

  -- Status & approval
  `status`           ENUM('pending','approved','rejected','active','expired','paused')
                     NOT NULL DEFAULT 'pending',
  `rejection_reason` VARCHAR(300)         NULL DEFAULT NULL,
  `approved_by`      INT UNSIGNED         NULL DEFAULT NULL,
  `approved_at`      DATETIME             NULL DEFAULT NULL,

  -- Tracking
  `impression_count` INT UNSIGNED     NOT NULL DEFAULT 0,
  `click_count`      INT UNSIGNED     NOT NULL DEFAULT 0,

  -- Submission
  `submitted_by`     INT UNSIGNED     NOT NULL,
  `notes`            TEXT                 NULL DEFAULT NULL,
  `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_bad_status`   (`status`, `valid_from`, `valid_until`),
  KEY `idx_bad_slot`     (`slot_id`, `display_type`),
  KEY `idx_bad_district` (`district_id`),
  KEY `idx_bad_category` (`category_id`),
  KEY `fk_bad_submitted` (`submitted_by`),

  CONSTRAINT `fk_bad_slot`      FOREIGN KEY (`slot_id`)      REFERENCES `tn_ad_slots`   (`id`),
  CONSTRAINT `fk_bad_submitted` FOREIGN KEY (`submitted_by`) REFERENCES `tn_users`      (`id`),
  CONSTRAINT `fk_bad_approved`  FOREIGN KEY (`approved_by`)  REFERENCES `tn_users`      (`id`),
  CONSTRAINT `fk_bad_category`  FOREIGN KEY (`category_id`)  REFERENCES `tn_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── AD IMAGES (max 5 per ad) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `tn_ad_images` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `ad_id`      INT UNSIGNED     NOT NULL,
  `filepath`   VARCHAR(500)     NOT NULL,
  `alt_text`   VARCHAR(200)         NULL DEFAULT NULL,
  `link_url`   VARCHAR(500)         NULL DEFAULT NULL COMMENT 'Click destination URL',
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)       NOT NULL DEFAULT 1,
  `added_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ai_ad` (`ad_id`),
  CONSTRAINT `fk_ai_ad` FOREIGN KEY (`ad_id`) REFERENCES `tn_business_ads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── AD CLICK TRACKING ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tn_ad_clicks` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id`      INT UNSIGNED NOT NULL,
  `image_id`   INT UNSIGNED     NULL DEFAULT NULL,
  `ip_hash`    VARCHAR(64)      NULL DEFAULT NULL,
  `clicked_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ac_ad` (`ad_id`),
  CONSTRAINT `fk_ac_ad` FOREIGN KEY (`ad_id`) REFERENCES `tn_business_ads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
