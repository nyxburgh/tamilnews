-- ============================================================
-- TAMIL NEWS PORTAL — COMPLETE DATABASE
-- Version: 1.0 | Engine: InnoDB | Charset: utf8mb4
-- Includes: Core schema + Contributors + Readers + Ratings
-- Run this single file only. Do NOT run separately.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `Tamilnews_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `Tamilnews_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. ROLES & USERS
-- ============================================================

CREATE TABLE `tn_roles` (
  `id`         TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(50)      NOT NULL,
  `slug`       VARCHAR(50)      NOT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_role_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tn_roles` (`id`, `name`, `slug`) VALUES
  (1, 'Admin',    'admin'),
  (2, 'Editor',   'editor'),
  (3, 'Reporter', 'reporter');

CREATE TABLE `tn_users` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `role_id`    TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `name`       VARCHAR(100)     NOT NULL,
  `email`      VARCHAR(150)     NOT NULL,
  `password`   VARCHAR(255)     NOT NULL,
  `avatar`     VARCHAR(255)         NULL DEFAULT NULL,
  `is_active`  TINYINT(1)       NOT NULL DEFAULT 1,
  `last_login` DATETIME             NULL DEFAULT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`),
  KEY `fk_user_role` (`role_id`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `tn_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin user (password: Admin@123 - CHANGE THIS IMMEDIATELY)
INSERT INTO `tn_users` (`role_id`, `name`, `email`, `password`, `is_active`) VALUES
  (1, 'Admin', 'admin@tamilnews.com', '$2y$10$D.cZmo/ng0Cpnekl0zjuuOSLJHLjRDbVHiZGay/OHy9mRxL55RkhG', 1);
-- ^ replace this password before going live

CREATE TABLE `tn_sessions` (
  `id`         VARCHAR(128)  NOT NULL,
  `user_id`    INT UNSIGNED  NOT NULL,
  `ip`         VARCHAR(45)       NULL DEFAULT NULL,
  `user_agent` VARCHAR(255)      NULL DEFAULT NULL,
  `expires_at` DATETIME      NOT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_session_user` (`user_id`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tn_csrf_tokens` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED     NULL DEFAULT NULL,
  `token`      VARCHAR(128) NOT NULL,
  `expires_at` DATETIME     NOT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_csrf_token` (`token`),
  KEY `fk_csrf_user` (`user_id`),
  CONSTRAINT `fk_csrf_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. CATEGORIES
-- ============================================================

CREATE TABLE `tn_categories` (
  `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id`   SMALLINT UNSIGNED     NULL DEFAULT NULL,
  `name`        VARCHAR(100)      NOT NULL,
  `name_tamil`  VARCHAR(100)          NULL DEFAULT NULL,
  `slug`        VARCHAR(120)      NOT NULL,
  `description` TEXT                  NULL DEFAULT NULL,
  `image`       VARCHAR(255)          NULL DEFAULT NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)        NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_slug` (`slug`),
  KEY `fk_cat_parent` (`parent_id`),
  CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `tn_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tn_categories` (`id`, `parent_id`, `name`, `name_tamil`, `slug`, `sort_order`) VALUES
  (1, NULL, 'Tamil Nadu',       'தமிழ்நாடு',      'tamil-nadu',     1),
  (2, NULL, 'India',            'இந்தியா',         'india',          2),
  (3, NULL, 'World',            'உலகம்',           'world',          3),
  (4, NULL, 'Cinema',           'சினிமா',          'cinema',         4),
  (5, NULL, 'Sports',           'விளையாட்டு',      'sports',         5),
  (6, NULL, 'Technology',       'தொழில்நுட்பம்',   'technology',     6),
  (7, NULL, 'Spiritual',        'ஆன்மீகம்',        'spiritual',      7),
  (8, NULL, 'Jobs & Education', 'வேலை & கல்வி',    'jobs-education', 8);

-- ============================================================
-- 3. LOCATIONS
-- ============================================================

CREATE TABLE `tn_states` (
  `id`        SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(100)      NOT NULL,
  `slug`      VARCHAR(120)      NOT NULL,
  `is_active` TINYINT(1)        NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_state_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tn_states` (`id`, `name`, `slug`) VALUES (1, 'Tamil Nadu', 'tamil-nadu');

CREATE TABLE `tn_districts` (
  `id`        SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `state_id`  SMALLINT UNSIGNED NOT NULL,
  `name`      VARCHAR(100)      NOT NULL,
  `slug`      VARCHAR(120)      NOT NULL,
  `is_active` TINYINT(1)        NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dist_slug` (`slug`),
  KEY `fk_dist_state` (`state_id`),
  CONSTRAINT `fk_dist_state` FOREIGN KEY (`state_id`) REFERENCES `tn_states` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add your districts via admin panel or insert here
INSERT INTO `tn_districts` (`id`, `state_id`, `name`, `slug`) VALUES
  (1, 1, 'Chennai',     'chennai-district'),
  (2, 1, 'Madurai',     'madurai-district'),
  (3, 1, 'Coimbatore',  'coimbatore-district'),
  (4, 1, 'Salem',       'salem-district'),
  (5, 1, 'Trichy',      'trichy-district'),
  (6, 1, 'Tirunelveli', 'tirunelveli-district');

CREATE TABLE `tn_cities` (
  `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `district_id` SMALLINT UNSIGNED NOT NULL,
  `name`        VARCHAR(100)      NOT NULL,
  `slug`        VARCHAR(120)      NOT NULL,
  `is_active`   TINYINT(1)        NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_city_slug` (`slug`),
  KEY `fk_city_dist` (`district_id`),
  CONSTRAINT `fk_city_dist` FOREIGN KEY (`district_id`) REFERENCES `tn_districts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tn_cities` (`id`, `district_id`, `name`, `slug`) VALUES
  (1, 1, 'Chennai',     'chennai'),
  (2, 2, 'Madurai',     'madurai'),
  (3, 3, 'Coimbatore',  'coimbatore'),
  (4, 4, 'Salem',       'salem'),
  (5, 5, 'Trichy',      'trichy'),
  (6, 6, 'Tirunelveli', 'tirunelveli');

-- ============================================================
-- 4. TAGS
-- ============================================================

CREATE TABLE `tn_tags` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `name_tamil`  VARCHAR(100)     NULL DEFAULT NULL,
  `slug`        VARCHAR(120) NOT NULL,
  `usage_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. MEDIA LIBRARY
-- ============================================================

CREATE TABLE `tn_media` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED         NULL DEFAULT NULL,
  `filename`   VARCHAR(255)     NOT NULL,
  `filepath`   VARCHAR(500)     NOT NULL,
  `thumb_path` VARCHAR(500)         NULL DEFAULT NULL,
  `mime_type`  VARCHAR(100)     NOT NULL,
  `size`       INT UNSIGNED     NOT NULL DEFAULT 0,
  `width`      SMALLINT UNSIGNED    NULL DEFAULT NULL,
  `height`     SMALLINT UNSIGNED    NULL DEFAULT NULL,
  `alt_text`   VARCHAR(255)         NULL DEFAULT NULL,
  `folder`     VARCHAR(100)     NOT NULL DEFAULT 'general',
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_media_user` (`user_id`),
  CONSTRAINT `fk_media_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. CONTRIBUTORS (External article writers — Google OAuth)
-- ============================================================

CREATE TABLE `tn_contributors` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `google_id`  VARCHAR(100)     NULL DEFAULT NULL,
  `name`       VARCHAR(150) NOT NULL,
  `email`      VARCHAR(200) NOT NULL,
  `avatar`     VARCHAR(500)     NULL DEFAULT NULL,
  `bio`        TEXT             NULL DEFAULT NULL,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 0,
  `last_login` DATETIME         NULL DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contributor_email`     (`email`),
  UNIQUE KEY `uq_contributor_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tn_contributor_categories` (
  `contributor_id` INT UNSIGNED      NOT NULL,
  `category_id`    SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (`contributor_id`, `category_id`),
  KEY `fk_cc_category` (`category_id`),
  CONSTRAINT `fk_cc_contributor` FOREIGN KEY (`contributor_id`) REFERENCES `tn_contributors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cc_category`    FOREIGN KEY (`category_id`)    REFERENCES `tn_categories`   (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. ARTICLES (CORE CONTENT TABLE)
-- ============================================================

CREATE TABLE `tn_articles` (
  `id`                INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  `user_id`           INT UNSIGNED      NOT NULL,
  `contributor_id`    INT UNSIGNED          NULL DEFAULT NULL,
  `category_id`       SMALLINT UNSIGNED NOT NULL,
  `city_id`           SMALLINT UNSIGNED     NULL DEFAULT NULL,
  `media_id`          INT UNSIGNED          NULL DEFAULT NULL,
  `title`             VARCHAR(500)      NOT NULL,
  `slug`              VARCHAR(550)      NOT NULL,
  `excerpt`           TEXT                  NULL DEFAULT NULL,
  `content`           LONGTEXT          NOT NULL,
  `content_type`      ENUM('news','video','short_news','live_update','gallery') NOT NULL DEFAULT 'news',
  `youtube_url`       VARCHAR(500)          NULL DEFAULT NULL,
  `youtube_video_id`  VARCHAR(20)           NULL DEFAULT NULL,
  `status`            ENUM('draft','review','published','scheduled','rejected') NOT NULL DEFAULT 'draft',
  `is_breaking`       TINYINT(1)        NOT NULL DEFAULT 0,
  `is_editors_pick`   TINYINT(1)        NOT NULL DEFAULT 0,
  `is_featured`       TINYINT(1)        NOT NULL DEFAULT 0,
  `is_auto_imported`  TINYINT(1)        NOT NULL DEFAULT 0,
  `import_source`     ENUM('manual','youtube','rss','contributor') NOT NULL DEFAULT 'manual',
  `source_url`        VARCHAR(500)          NULL DEFAULT NULL,
  `source_hash`       VARCHAR(64)           NULL DEFAULT NULL,
  `breaking_expires_at` DATETIME             NULL DEFAULT NULL,
  `read_time`         TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  `view_count`        INT UNSIGNED      NOT NULL DEFAULT 0,
  `whatsapp_shares`   INT UNSIGNED      NOT NULL DEFAULT 0,
  `rating_avg`        DECIMAL(3,2)      NOT NULL DEFAULT 0.00,
  `rating_count`      INT UNSIGNED      NOT NULL DEFAULT 0,
  `meta_title`        VARCHAR(300)          NULL DEFAULT NULL,
  `meta_desc`         VARCHAR(500)          NULL DEFAULT NULL,
  `schema_type`       ENUM('NewsArticle','VideoObject') NOT NULL DEFAULT 'NewsArticle',
  `related_override`  JSON                  NULL DEFAULT NULL,
  `published_at`      DATETIME              NULL DEFAULT NULL,
  `scheduled_at`      DATETIME              NULL DEFAULT NULL,
  `created_at`        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_article_slug` (`slug`),
  KEY `idx_article_status`    (`status`),
  KEY `idx_article_breaking`  (`is_breaking`),
  KEY `idx_article_featured`  (`is_featured`),
  KEY `idx_article_published` (`published_at`),
  KEY `idx_article_category`  (`category_id`),
  KEY `fk_article_user`       (`user_id`),
  KEY `fk_article_contributor`(`contributor_id`),
  KEY `fk_article_city`       (`city_id`),
  KEY `fk_article_media`      (`media_id`),
  KEY `idx_source_hash`       (`source_hash`),
  CONSTRAINT `fk_article_user`        FOREIGN KEY (`user_id`)        REFERENCES `tn_users`       (`id`),
  CONSTRAINT `fk_article_contributor` FOREIGN KEY (`contributor_id`) REFERENCES `tn_contributors`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_article_category`    FOREIGN KEY (`category_id`)    REFERENCES `tn_categories`  (`id`),
  CONSTRAINT `fk_article_city`        FOREIGN KEY (`city_id`)        REFERENCES `tn_cities`      (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_article_media`       FOREIGN KEY (`media_id`)       REFERENCES `tn_media`       (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. ARTICLE TAGS (pivot)
-- ============================================================

CREATE TABLE `tn_article_tags` (
  `article_id` INT UNSIGNED NOT NULL,
  `tag_id`     INT UNSIGNED NOT NULL,
  PRIMARY KEY (`article_id`, `tag_id`),
  KEY `fk_at_tag` (`tag_id`),
  CONSTRAINT `fk_at_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_at_tag`     FOREIGN KEY (`tag_id`)     REFERENCES `tn_tags`     (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. ANALYTICS
-- ============================================================

CREATE TABLE `tn_analytics_daily` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` INT UNSIGNED NOT NULL,
  `date`       DATE         NOT NULL,
  `views`      INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_analytics_art_date` (`article_id`, `date`),
  KEY `idx_analytics_date` (`date`),
  CONSTRAINT `fk_analytics_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. YOUTUBE AUTOMATION
-- ============================================================

CREATE TABLE `tn_youtube_channels` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `channel_id`     VARCHAR(100) NOT NULL,
  `channel_name`   VARCHAR(200) NOT NULL,
  `playlist_id`    VARCHAR(100)     NULL DEFAULT NULL,
  `category_id`    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `auto_publish`   TINYINT(1)   NOT NULL DEFAULT 0,
  `fetch_interval` ENUM('hourly','daily') NOT NULL DEFAULT 'hourly',
  `last_fetched_at` DATETIME        NULL DEFAULT NULL,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_yt_channel_id` (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tn_youtube_keyword_map` (
  `id`          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  `channel_id`  INT UNSIGNED      NOT NULL,
  `keyword`     VARCHAR(100)      NOT NULL,
  `category_id` SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_kw_channel`  (`channel_id`),
  KEY `fk_kw_category` (`category_id`),
  CONSTRAINT `fk_kw_channel`  FOREIGN KEY (`channel_id`)  REFERENCES `tn_youtube_channels`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kw_category` FOREIGN KEY (`category_id`) REFERENCES `tn_categories`       (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tn_youtube_imports` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `channel_id`  INT UNSIGNED NOT NULL,
  `article_id`  INT UNSIGNED     NULL DEFAULT NULL,
  `video_id`    VARCHAR(20)  NOT NULL,
  `title`       VARCHAR(500) NOT NULL,
  `description` TEXT             NULL DEFAULT NULL,
  `thumbnail`   VARCHAR(500)     NULL DEFAULT NULL,
  `published_at` DATETIME        NULL DEFAULT NULL,
  `status`      ENUM('pending','imported','skipped') NOT NULL DEFAULT 'pending',
  `imported_at` DATETIME         NULL DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_yt_import_video` (`video_id`),
  KEY `fk_yi_channel` (`channel_id`),
  KEY `fk_yi_article` (`article_id`),
  CONSTRAINT `fk_yi_channel` FOREIGN KEY (`channel_id`) REFERENCES `tn_youtube_channels`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_yi_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles`         (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. RSS FEEDS
-- ============================================================

CREATE TABLE `tn_rss_feeds` (
  `id`              INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(200)      NOT NULL,
  `url`             VARCHAR(500)      NOT NULL,
  `category_id`     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `fetch_interval`  SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  `last_fetched_at` DATETIME              NULL DEFAULT NULL,
  `is_active`       TINYINT(1)        NOT NULL DEFAULT 1,
  `created_at`      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tn_rss_imports` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `feed_id`     INT UNSIGNED NOT NULL,
  `article_id`  INT UNSIGNED     NULL DEFAULT NULL,
  `title`       VARCHAR(500) NOT NULL,
  `source_url`  VARCHAR(500) NOT NULL,
  `source_hash` VARCHAR(64)  NOT NULL,
  `status`      ENUM('pending','imported','skipped') NOT NULL DEFAULT 'pending',
  `fetched_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rss_hash`    (`source_hash`),
  KEY `fk_ri_feed`    (`feed_id`),
  KEY `fk_ri_article` (`article_id`),
  CONSTRAINT `fk_ri_feed`    FOREIGN KEY (`feed_id`)    REFERENCES `tn_rss_feeds`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ri_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. FCM PUSH NOTIFICATIONS
-- ============================================================

CREATE TABLE `tn_fcm_topics` (
  `id`        TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(100)     NOT NULL,
  `slug`      VARCHAR(100)     NOT NULL,
  `is_active` TINYINT(1)       NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fcm_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tn_fcm_topics` (`name`, `slug`) VALUES
  ('General News',   'general'),
  ('Breaking News',  'breaking'),
  ('Cinema',         'cinema'),
  ('Sports',         'sports');

CREATE TABLE `tn_push_notifications` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED         NULL DEFAULT NULL,
  `topic_id`   TINYINT UNSIGNED     NULL DEFAULT NULL,
  `title`      VARCHAR(255)     NOT NULL,
  `body`       TEXT             NOT NULL,
  `status`     ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `sent_at`    DATETIME             NULL DEFAULT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pn_user`  (`user_id`),
  KEY `fk_pn_topic` (`topic_id`),
  CONSTRAINT `fk_pn_user`  FOREIGN KEY (`user_id`)  REFERENCES `tn_users`      (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pn_topic` FOREIGN KEY (`topic_id`) REFERENCES `tn_fcm_topics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. ADVERTISEMENT SLOTS
-- ============================================================

CREATE TABLE `tn_ad_slots` (
  `id`           TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(100)     NOT NULL,
  `slug`         VARCHAR(100)     NOT NULL,
  `position`     ENUM('header','in_article_after_p3','in_article_after_p6','sidebar','footer') NOT NULL,
  `ad_code`      TEXT                 NULL DEFAULT NULL,
  `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
  `desktop_size` VARCHAR(20)          NULL DEFAULT NULL,
  `mobile_size`  VARCHAR(20)          NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ad_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tn_ad_slots` (`name`, `slug`, `position`, `desktop_size`, `mobile_size`) VALUES
  ('Header Banner',       'header',        'header',              '728x90',  '320x50'),
  ('In-Article (Para 3)', 'in-article-p3', 'in_article_after_p3', '728x90',  '320x100'),
  ('In-Article (Para 6)', 'in-article-p6', 'in_article_after_p6', '728x90',  '320x100'),
  ('Sidebar Widget',      'sidebar',       'sidebar',             '300x250', '300x250'),
  ('Footer Banner',       'footer',        'footer',              '728x90',  '320x50');

-- ============================================================
-- 14. SEO CONFIG
-- ============================================================

CREATE TABLE `tn_seo_config` (
  `id`         TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_type`  VARCHAR(50)      NOT NULL,
  `meta_title` VARCHAR(300)         NULL DEFAULT NULL,
  `meta_desc`  VARCHAR(500)         NULL DEFAULT NULL,
  `og_image`   VARCHAR(500)         NULL DEFAULT NULL,
  `updated_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seo_page` (`page_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tn_seo_config` (`page_type`, `meta_title`, `meta_desc`) VALUES
  ('homepage', 'Tamil News Portal | Latest Tamil News', 'Read the latest Tamil news, breaking news, cinema, sports, and more.'),
  ('category', NULL, NULL),
  ('article',  NULL, NULL),
  ('video',    NULL, NULL);

-- ============================================================
-- 15. APP SETTINGS
-- ============================================================

CREATE TABLE `tn_settings` (
  `id`         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group`      VARCHAR(50)       NOT NULL DEFAULT 'general',
  `key`        VARCHAR(100)      NOT NULL,
  `value`      TEXT                  NULL DEFAULT NULL,
  `label`      VARCHAR(200)          NULL DEFAULT NULL,
  `input_type` VARCHAR(30)       NOT NULL DEFAULT 'text',
  `updated_at` TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tn_settings` (`group`, `key`, `value`, `label`, `input_type`) VALUES
  ('general',  'site_name',              'Tamil News Portal', 'Site Name',              'text'),
  ('general',  'site_url',               'http://localhost',  'Site URL',               'text'),
  ('general',  'site_tagline',           'Latest Tamil News', 'Site Tagline',           'text'),
  ('general',  'site_logo',              NULL,                'Site Logo URL',          'image'),
  ('general',  'contact_email',          NULL,                'Contact Email',          'email'),
  ('general',  'articles_per_page',      '12',                'Articles Per Page',      'number'),
  ('breaking', 'ticker_enabled',         '1',                 'Ticker Enabled',         'toggle'),
  ('breaking', 'ticker_speed',           '50',                'Ticker Speed',           'number'),
  ('breaking', 'breaking_expiry_hours',  '6',                 'Breaking Expiry (hrs)',  'number'),
  ('youtube',  'api_key',                NULL,                'YouTube API Key',        'text'),
  ('youtube',  'fetch_interval',         'hourly',            'Fetch Interval',         'select'),
  ('youtube',  'default_auto_publish',   '0',                 'Auto-publish by default','toggle'),
  ('rss',      'fetch_interval_min',     '30',                'RSS Fetch Interval (min)','number'),
  ('fcm',      'server_key',             NULL,                'FCM Server Key',         'text'),
  ('fcm',      'sender_id',              NULL,                'FCM Sender ID',          'text'),
  ('social',   'facebook_url',           NULL,                'Facebook Page URL',      'text'),
  ('social',   'twitter_url',            NULL,                'Twitter/X URL',          'text'),
  ('social',   'youtube_url',            NULL,                'YouTube Channel URL',    'text'),
  ('social',   'whatsapp_number',        NULL,                'WhatsApp Number',        'text'),
  ('cache',    'homepage_cache_ttl',     '300',               'Homepage Cache TTL (sec)','number'),
  ('cache',    'category_cache_ttl',     '300',               'Category Cache TTL (sec)','number'),
  ('seo',      'google_analytics_id',    NULL,                'GA Measurement ID',      'text'),
  ('seo',      'google_news_enabled',    '1',                 'Google News Sitemap',    'toggle'),
  ('admin',    'ip_whitelist',           NULL,                'Admin IP Whitelist',     'textarea'),
  ('admin',    'related_articles_count', '4',                 'Related Articles Count', 'number'),
  ('oauth',    'google_client_id',       NULL,                'Google OAuth Client ID', 'text'),
  ('oauth',    'google_client_secret',   NULL,                'Google OAuth Client Secret','text'),
  ('oauth',    'contributor_portal',     '1',                 'Contributor Portal Enabled','toggle'),
  ('oauth',    'reader_login',           '1',                 'Reader Login Enabled',   'toggle');

-- ============================================================
-- 16. CRON LOGS
-- ============================================================

CREATE TABLE `tn_cron_logs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job`         VARCHAR(100) NOT NULL,
  `status`      ENUM('success','error') NOT NULL DEFAULT 'success',
  `message`     TEXT             NULL DEFAULT NULL,
  `records`     INT UNSIGNED NOT NULL DEFAULT 0,
  `duration_ms` INT UNSIGNED NOT NULL DEFAULT 0,
  `ran_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cron_job` (`job`),
  KEY `idx_cron_ran` (`ran_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 17. ACTIVITY LOG
-- ============================================================

CREATE TABLE `tn_activity_log` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED        NULL DEFAULT NULL,
  `action`      VARCHAR(100)    NOT NULL,
  `entity`      VARCHAR(50)         NULL DEFAULT NULL,
  `entity_id`   INT UNSIGNED        NULL DEFAULT NULL,
  `description` VARCHAR(500)        NULL DEFAULT NULL,
  `ip`          VARCHAR(45)         NULL DEFAULT NULL,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_al_user`    (`user_id`),
  KEY `idx_al_entity` (`entity`, `entity_id`),
  CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 18. INTERNAL LINKING CONFIG
-- ============================================================

CREATE TABLE `tn_linking_config` (
  `id`             TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `match_by`       SET('category','tags','location') NOT NULL DEFAULT 'category,tags',
  `count`          TINYINT UNSIGNED NOT NULL DEFAULT 4,
  `allow_override` TINYINT(1)       NOT NULL DEFAULT 1,
  `updated_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tn_linking_config` (`match_by`, `count`, `allow_override`) VALUES ('category,tags', 4, 1);

-- ============================================================
-- 19. SITEMAP LOG
-- ============================================================

CREATE TABLE `tn_sitemap_log` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`         ENUM('news','standard') NOT NULL DEFAULT 'news',
  `generated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url_count`    INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 20. READERS (Google OAuth — frontend login for ratings)
-- ============================================================

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

-- ============================================================
-- 21. ARTICLE RATINGS & REVIEWS
-- ============================================================

CREATE TABLE `tn_article_ratings` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `article_id` INT UNSIGNED     NOT NULL,
  `reader_id`  INT UNSIGNED     NOT NULL,
  `rating`     TINYINT UNSIGNED NOT NULL COMMENT '1-5',
  `review`     TEXT                 NULL DEFAULT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rating_article_reader` (`article_id`, `reader_id`),
  KEY `fk_rating_article` (`article_id`),
  KEY `fk_rating_reader`  (`reader_id`),
  CONSTRAINT `fk_rating_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rating_reader`  FOREIGN KEY (`reader_id`)  REFERENCES `tn_readers`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 22. PREMIUM (ready for future subscription billing)
-- ============================================================

CREATE TABLE `tn_premium_plans` (
  `id`            TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(100)     NOT NULL,
  `name_tamil`    VARCHAR(100)         NULL DEFAULT NULL,
  `price_inr`     DECIMAL(8,2)     NOT NULL DEFAULT 0.00,
  `duration_days` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Placeholder plans (prices to be set via admin)
INSERT INTO `tn_premium_plans` (`name`, `name_tamil`, `price_inr`, `duration_days`, `is_active`) VALUES
  ('Monthly',  'மாதாந்திர', 99.00,  30,  0),
  ('Annual',   'ஆண்டு',     799.00, 365, 0);

CREATE TABLE `tn_premium_access` (
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
  KEY `idx_pa_expires` (`expires_at`),
  CONSTRAINT `fk_pa_reader` FOREIGN KEY (`reader_id`) REFERENCES `tn_readers`       (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pa_plan`   FOREIGN KEY (`plan_id`)   REFERENCES `tn_premium_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END — Tamil News Portal Complete Schema
-- Tables: 30 | Prefix: tn_
-- ============================================================
