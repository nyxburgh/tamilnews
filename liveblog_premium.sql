-- ============================================================
-- TAMIL NEWS PORTAL — LIVE BLOG + PREMIUM
-- Run AFTER tamilnews_complete.sql
-- ============================================================

USE `Tamilnews_db`;

-- ── LIVE BLOGS ────────────────────────────────────────────────

CREATE TABLE `tn_live_blogs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `article_id`  INT UNSIGNED     NULL DEFAULT NULL,
  `title`       VARCHAR(300) NOT NULL,
  `slug`        VARCHAR(320) NOT NULL,
  `description` TEXT             NULL DEFAULT NULL,
  `type`        ENUM('general','election','cricket','football','sports','disaster','budget') NOT NULL DEFAULT 'general',
  `team_home`   VARCHAR(100)     NULL DEFAULT NULL,
  `team_away`   VARCHAR(100)     NULL DEFAULT NULL,
  `score_home`  VARCHAR(50)      NULL DEFAULT NULL,
  `score_away`  VARCHAR(50)      NULL DEFAULT NULL,
  `status`      ENUM('active','ended') NOT NULL DEFAULT 'active',
  `started_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at`    DATETIME         NULL DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_live_slug` (`slug`),
  KEY `fk_lb_user`    (`user_id`),
  KEY `fk_lb_article` (`article_id`),
  KEY `idx_lb_status` (`status`),
  CONSTRAINT `fk_lb_user`    FOREIGN KEY (`user_id`)    REFERENCES `tn_users`    (`id`),
  CONSTRAINT `fk_lb_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── LIVE ENTRIES ──────────────────────────────────────────────

CREATE TABLE `tn_live_entries` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `live_blog_id` INT UNSIGNED NOT NULL,
  `user_id`      INT UNSIGNED     NULL DEFAULT NULL,
  `content`      TEXT         NOT NULL,
  `label`        VARCHAR(50)      NULL DEFAULT NULL,
  `label_color`  VARCHAR(20)  NOT NULL DEFAULT '#C0001A',
  `score_home`   VARCHAR(50)      NULL DEFAULT NULL,
  `score_away`   VARCHAR(50)      NULL DEFAULT NULL,
  `is_pinned`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_le_blog` (`live_blog_id`),
  KEY `fk_le_user` (`user_id`),
  KEY `idx_le_created` (`live_blog_id`, `created_at`),
  CONSTRAINT `fk_le_blog` FOREIGN KEY (`live_blog_id`) REFERENCES `tn_live_blogs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_le_user` FOREIGN KEY (`user_id`)      REFERENCES `tn_users`      (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PREMIUM COLUMNS (if not already added) ───────────────────
-- Skip if you already ran special_categories.sql which adds these

ALTER TABLE `tn_articles`
  ADD COLUMN IF NOT EXISTS `is_premium`     TINYINT(1)   NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `premium_set_by` INT UNSIGNED     NULL DEFAULT NULL;

-- ── PREMIUM PLANS (if not already created) ───────────────────
CREATE TABLE IF NOT EXISTS `tn_premium_plans` (
  `id`            TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(100)     NOT NULL,
  `name_tamil`    VARCHAR(100)         NULL DEFAULT NULL,
  `price_inr`     DECIMAL(8,2)     NOT NULL DEFAULT 0.00,
  `duration_days` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `tn_premium_plans` (`name`, `name_tamil`, `price_inr`, `duration_days`, `is_active`) VALUES
  ('Monthly',  'மாதாந்திர', 99.00,  30,  0),
  ('Annual',   'ஆண்டு',     799.00, 365, 0);

CREATE TABLE IF NOT EXISTS `tn_premium_access` (
  `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `reader_id`   INT UNSIGNED     NOT NULL,
  `plan_id`     TINYINT UNSIGNED NOT NULL,
  `starts_at`   DATETIME         NOT NULL,
  `expires_at`  DATETIME         NOT NULL,
  `status`      ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `payment_ref` VARCHAR(200)         NULL DEFAULT NULL,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pa_reader` (`reader_id`),
  KEY `fk_pa_plan`   (`plan_id`),
  CONSTRAINT `fk_pa_reader2` FOREIGN KEY (`reader_id`) REFERENCES `tn_readers`       (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pa_plan2`   FOREIGN KEY (`plan_id`)   REFERENCES `tn_premium_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DONE
-- New tables: tn_live_blogs, tn_live_entries
-- New columns: tn_articles.is_premium, tn_articles.premium_set_by
-- ============================================================
