-- ============================================================
-- TAMIL NEWS PORTAL — ADDITIONS FOR CONTRIBUTORS & READERS
-- Add to tamilnews_db.sql (append after existing tables)
-- ============================================================

-- ── ALTER tn_articles: add contributor_id + rating columns ──

ALTER TABLE `tn_articles`
  ADD COLUMN `contributor_id` INT UNSIGNED NULL DEFAULT NULL AFTER `user_id`,
  ADD COLUMN `rating_avg`     DECIMAL(3,2) NOT NULL DEFAULT 0.00 AFTER `whatsapp_shares`,
  ADD COLUMN `rating_count`   INT UNSIGNED NOT NULL DEFAULT 0 AFTER `rating_avg`,
  ADD KEY `fk_article_contributor` (`contributor_id`),
  ADD CONSTRAINT `fk_article_contributor`
    FOREIGN KEY (`contributor_id`) REFERENCES `tn_contributors` (`id`) ON DELETE SET NULL;

-- Note: Run tn_contributors table FIRST before this ALTER

-- ── CONTRIBUTORS ─────────────────────────────────────────────

CREATE TABLE `tn_contributors` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `google_id`   VARCHAR(100)      NULL DEFAULT NULL,
  `name`        VARCHAR(150)  NOT NULL,
  `email`       VARCHAR(200)  NOT NULL,
  `avatar`      VARCHAR(500)      NULL DEFAULT NULL,
  `bio`         TEXT              NULL DEFAULT NULL,
  `is_active`   TINYINT(1)    NOT NULL DEFAULT 0,
  `last_login`  DATETIME          NULL DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contributor_email`     (`email`),
  UNIQUE KEY `uq_contributor_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── CONTRIBUTOR ↔ CATEGORY ASSIGNMENTS ───────────────────────

CREATE TABLE `tn_contributor_categories` (
  `contributor_id` INT UNSIGNED      NOT NULL,
  `category_id`    SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (`contributor_id`, `category_id`),
  KEY `fk_cc_category` (`category_id`),
  CONSTRAINT `fk_cc_contributor` FOREIGN KEY (`contributor_id`) REFERENCES `tn_contributors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cc_category`    FOREIGN KEY (`category_id`)    REFERENCES `tn_categories`   (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── READERS (Google OAuth only) ───────────────────────────────

CREATE TABLE `tn_readers` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `google_id`  VARCHAR(100) NOT NULL,
  `name`       VARCHAR(150) NOT NULL,
  `email`      VARCHAR(200) NOT NULL,
  `avatar`     VARCHAR(500)     NULL DEFAULT NULL,
  `last_login` DATETIME         NULL DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reader_google_id` (`google_id`),
  UNIQUE KEY `uq_reader_email`     (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ARTICLE RATINGS ───────────────────────────────────────────

CREATE TABLE `tn_article_ratings` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id`  INT UNSIGNED NOT NULL,
  `reader_id`   INT UNSIGNED NOT NULL,
  `rating`      TINYINT UNSIGNED NOT NULL COMMENT '1–5',
  `review`      TEXT             NULL DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rating_article_reader` (`article_id`, `reader_id`),
  KEY `fk_rating_article` (`article_id`),
  KEY `fk_rating_reader`  (`reader_id`),
  CONSTRAINT `fk_rating_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rating_reader`  FOREIGN KEY (`reader_id`)  REFERENCES `tn_readers`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ADD GOOGLE OAUTH KEYS TO tn_settings ─────────────────────

INSERT INTO `tn_settings` (`group`, `key`, `value`, `label`, `input_type`) VALUES
  ('oauth', 'google_client_id',     NULL, 'Google OAuth Client ID',     'text'),
  ('oauth', 'google_client_secret', NULL, 'Google OAuth Client Secret', 'text'),
  ('oauth', 'contributor_portal',   '1',  'Contributor Portal Enabled', 'toggle'),
  ('oauth', 'reader_login',         '1',  'Reader Login Enabled',       'toggle');

-- ============================================================
-- END OF ADDITIONS
-- ============================================================
