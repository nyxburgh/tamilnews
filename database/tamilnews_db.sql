-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2026 at 09:34 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tamilnews_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tn_activity_log`
--

CREATE TABLE `tn_activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity` varchar(50) DEFAULT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_ad_slots`
--

CREATE TABLE `tn_ad_slots` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `position` enum('header','in_article_after_p3','in_article_after_p6','sidebar','footer') NOT NULL,
  `ad_code` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `desktop_size` varchar(20) DEFAULT NULL,
  `mobile_size` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_ad_slots`
--

INSERT INTO `tn_ad_slots` (`id`, `name`, `slug`, `position`, `ad_code`, `is_active`, `desktop_size`, `mobile_size`, `updated_at`) VALUES
(1, 'Header Banner', 'header', 'header', NULL, 1, '728x90', '320x50', '2026-04-20 06:36:28'),
(2, 'In-Article (Para 3)', 'in-article-p3', 'in_article_after_p3', NULL, 1, '728x90', '320x100', '2026-04-20 06:36:28'),
(3, 'In-Article (Para 6)', 'in-article-p6', 'in_article_after_p6', NULL, 1, '728x90', '320x100', '2026-04-20 06:36:28'),
(4, 'Sidebar Widget', 'sidebar', 'sidebar', NULL, 1, '300x250', '300x250', '2026-04-20 06:36:28'),
(5, 'Footer Banner', 'footer', 'footer', NULL, 1, '728x90', '320x50', '2026-04-20 06:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `tn_analytics_daily`
--

CREATE TABLE `tn_analytics_daily` (
  `id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_analytics_daily`
--

INSERT INTO `tn_analytics_daily` (`id`, `article_id`, `date`, `views`) VALUES
(1, 40, '2026-04-20', 8),
(2, 43, '2026-04-20', 3),
(3, 35, '2026-04-20', 3),
(4, 39, '2026-04-20', 3),
(5, 41, '2026-04-20', 5),
(23, 40, '2026-04-27', 3),
(24, 36, '2026-04-27', 1),
(26, 43, '2026-04-27', 1),
(28, 40, '2026-04-28', 1),
(29, 40, '2026-04-29', 3),
(31, 35, '2026-04-29', 1),
(32, 43, '2026-04-29', 1),
(33, 36, '2026-04-29', 1),
(35, 40, '2026-05-05', 3),
(38, 35, '2026-05-05', 1),
(39, 40, '2026-05-06', 2),
(40, 35, '2026-05-06', 1),
(42, 38, '2026-05-06', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tn_articles`
--

CREATE TABLE `tn_articles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `contributor_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` smallint(5) UNSIGNED NOT NULL,
  `city_id` smallint(5) UNSIGNED DEFAULT NULL,
  `media_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(500) NOT NULL,
  `slug` varchar(550) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `excerpt_en` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `content_en` longtext DEFAULT NULL,
  `content_type` enum('news','video','short_news','live_update','gallery') NOT NULL DEFAULT 'news',
  `language` enum('ta','en','both') NOT NULL DEFAULT 'ta',
  `youtube_url` varchar(500) DEFAULT NULL,
  `youtube_video_id` varchar(20) DEFAULT NULL,
  `status` enum('draft','review','published','scheduled','rejected') NOT NULL DEFAULT 'draft',
  `approval_stage` enum('reporter','district_editor','chief_editor','published') NOT NULL DEFAULT 'reporter',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `district_approved_by` int(10) UNSIGNED DEFAULT NULL,
  `district_approved_at` datetime DEFAULT NULL,
  `is_breaking` tinyint(1) NOT NULL DEFAULT 0,
  `is_editors_pick` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  `premium_set_by` int(10) UNSIGNED DEFAULT NULL,
  `is_auto_imported` tinyint(1) NOT NULL DEFAULT 0,
  `import_source` enum('manual','youtube','rss','contributor') NOT NULL DEFAULT 'manual',
  `source_url` varchar(500) DEFAULT NULL,
  `source_hash` varchar(64) DEFAULT NULL,
  `breaking_expires_at` datetime DEFAULT NULL,
  `read_time` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `word_count` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `image_caption` varchar(300) DEFAULT NULL,
  `image_credit` varchar(150) DEFAULT NULL,
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `whatsapp_shares` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `rating_avg` decimal(3,2) NOT NULL DEFAULT 0.00,
  `rating_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `meta_title` varchar(300) DEFAULT NULL,
  `meta_desc` varchar(500) DEFAULT NULL,
  `schema_type` enum('NewsArticle','VideoObject') NOT NULL DEFAULT 'NewsArticle',
  `related_override` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`related_override`)),
  `series_id` int(10) UNSIGNED DEFAULT NULL,
  `series_part` tinyint(3) UNSIGNED DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pending_edit` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pending_edit`)),
  `pending_edit_by` int(10) UNSIGNED DEFAULT NULL,
  `pending_edit_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_articles`
--

INSERT INTO `tn_articles` (`id`, `user_id`, `contributor_id`, `category_id`, `city_id`, `media_id`, `title`, `slug`, `excerpt`, `excerpt_en`, `content`, `content_en`, `content_type`, `language`, `youtube_url`, `youtube_video_id`, `status`, `approval_stage`, `approved_by`, `approved_at`, `district_approved_by`, `district_approved_at`, `is_breaking`, `is_editors_pick`, `is_featured`, `is_premium`, `premium_set_by`, `is_auto_imported`, `import_source`, `source_url`, `source_hash`, `breaking_expires_at`, `read_time`, `word_count`, `image_caption`, `image_credit`, `view_count`, `whatsapp_shares`, `rating_avg`, `rating_count`, `meta_title`, `meta_desc`, `schema_type`, `related_override`, `series_id`, `series_part`, `published_at`, `scheduled_at`, `created_at`, `updated_at`, `pending_edit`, `pending_edit_by`, `pending_edit_at`) VALUES
(35, 2, NULL, 1, NULL, NULL, 'தமிழ்நாட்டில் புதிய மழை எச்சரிக்கை — வடகிழக்கு பருவமழை தீவிரமடைகிறது', 'tamilnadu-rain-warning-northeast-monsoon', 'வடகிழக்கு பருவமழை தீவிரமடைவதால் தமிழ்நாட்டின் பல மாவட்டங்களில் கனமழை பெய்யும் என வானிலை ஆய்வு மையம் எச்சரிக்கை விடுத்துள்ளது.', NULL, '<p>வடகிழக்கு பருவமழை தீவிரமடைவதால் தமிழ்நாட்டின் பல மாவட்டங்களில் கனமழை பெய்யும் என சென்னை வானிலை ஆய்வு மையம் எச்சரிக்கை விடுத்துள்ளது.</p>\r\n<p>சென்னை, கடலூர், விழுப்புரம், தஞ்சாவூர் மற்றும் நாகப்பட்டினம் மாவட்டங்களில் அடுத்த 48 மணி நேரத்திற்கு கனமழை பெய்யும் வாய்ப்புள்ளதாக தெரிவிக்கப்பட்டுள்ளது.</p>\r\n<p>மீனவர்களுக்கு கடலில் செல்ல வேண்டாம் என எச்சரிக்கை விடுக்கப்பட்டுள்ளது.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 0, 0, 1, 0, NULL, 0, 'manual', NULL, NULL, NULL, 2, 0, NULL, NULL, 1246, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 10:38:16', NULL, '2026-04-20 05:08:16', '2026-05-06 14:38:29', NULL, NULL, NULL),
(36, 2, NULL, 1, NULL, NULL, 'சென்னை மெட்ரோ புதிய நிலையங்கள் திறப்பு — பயணிகளுக்கு மகிழ்ச்சி', 'chennai-metro-new-stations-inauguration', 'சென்னை மெட்ரோ ரயில் திட்டத்தின் புதிய நிலையங்கள் திறப்பு விழா இன்று நடைபெற்றது.', NULL, '<p>சென்னை மெட்ரோ ரயில் திட்டத்தின் இரண்டாம் கட்ட விரிவாக்கத்தின் கீழ் புதிய நிலையங்கள் இன்று திறக்கப்பட்டன.</p><p>கோட்டூர்புரம் முதல் மடிப்பாக்கம் வரை நீண்டுள்ள இந்த புதிய பாதை பயணிகளுக்கு மிகவும் உதவியாக இருக்கும்.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 0, 1, 0, 0, NULL, 0, 'manual', NULL, NULL, NULL, 2, 0, NULL, NULL, 3452, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 07:38:16', NULL, '2026-04-20 02:08:16', '2026-04-29 07:38:16', NULL, NULL, NULL),
(37, 2, NULL, 1, NULL, NULL, 'கோவையில் புதிய IT பார்க் திட்டம் — 10,000 வேலை வாய்ப்புகள்', 'coimbatore-new-it-park-10000-jobs', 'கோவையில் புதிய தகவல் தொழில்நுட்ப பூங்கா அமைக்கப்படவுள்ளது.', NULL, '<p>கோவையில் 500 ஏக்கர் பரப்பளவில் புதிய IT பார்க் அமைக்கப்படவுள்ளது என தமிழ்நாடு அரசு அறிவித்துள்ளது.</p><p>இந்த திட்டத்தில் 50 நிறுவனங்கள் வந்து இயங்கும் என எதிர்பார்க்கப்படுகிறது. இதன் மூலம் 10,000 இளைஞர்களுக்கு வேலை வாய்ப்பு கிடைக்கும்.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, 0, 'manual', NULL, NULL, NULL, 2, 0, NULL, NULL, 890, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 04:38:16', NULL, '2026-04-19 23:08:16', '2026-04-20 07:08:16', NULL, NULL, NULL),
(38, 2, NULL, 2, NULL, NULL, 'மத்திய அரசு பட்ஜெட் 2025 — வருமான வரி விலக்கு உயர்வு அறிவிப்பு', 'central-budget-2025-income-tax-exemption-hike', 'மத்திய பட்ஜெட்டில் வருமான வரி விலக்கு வரம்பு 7 லட்சத்திலிருந்து 10 லட்சமாக உயர்த்தப்படும்.', NULL, '<p>நிதியமைச்சர் நாடாளுமன்றத்தில் மத்திய பட்ஜெட்டை தாக்கல் செய்தார்.</p><p>வருமான வரி விலக்கு வரம்பு 7 லட்சத்திலிருந்து 10 லட்சமாக உயர்த்தப்படுகிறது என்று அறிவிக்கப்பட்டுள்ளது.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 1, 0, 0, 0, NULL, 0, 'manual', NULL, NULL, NULL, 3, 0, NULL, NULL, 5672, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 09:38:16', NULL, '2026-04-20 04:08:16', '2026-05-06 14:39:06', NULL, NULL, NULL),
(39, 2, NULL, 2, NULL, NULL, 'இந்தியா-பாகிஸ்தான் உறவுகளில் மாற்றம் — வெளியுறவுத்துறை புதிய நிலைப்பாடு', 'india-pakistan-relations-new-foreign-policy-stance', 'இந்தியாவும் பாகிஸ்தானும் இராஜதந்திர பேச்சுவார்த்தைகளை மீண்டும் தொடங்க முடிவு செய்துள்ளன.', NULL, '<p>இந்திய வெளியுறவு அமைச்சகம் பாகிஸ்தானுடன் இராஜதந்திர உறவுகளை மேம்படுத்த முடிவு செய்துள்ளதாக தெரிவித்துள்ளது.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, 0, 'manual', NULL, NULL, NULL, 2, 0, NULL, NULL, 2103, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 00:38:16', NULL, '2026-04-19 19:08:16', '2026-04-20 13:29:03', NULL, NULL, NULL),
(40, 2, NULL, 4, NULL, NULL, 'விஜய்யின் புதிய படம் \"கோட்டை\" — First Look Poster வெளியீடு!', 'vijay-new-movie-kottai-first-look-poster-release', 'விஜய் நடிக்கும் புதிய படம் \"கோட்டை\"யின் First Look Poster இன்று வெளியிடப்பட்டது.', NULL, '<p>தமிழ் திரை உலகின் மாஸ் நடிகர் விஜய் நடிக்கும் புதிய படம் \"கோட்டை\"யின் First Look Poster இன்று வெளியிடப்பட்டது.</p>\r\n<p>இயக்குநர் ஏ.ஆர்.முருகதாஸ் இயக்கத்தில் உருவாகும் இந்த படத்தில் விஜய் ஒரு போலீஸ் அதிகாரியாக நடிக்கிறார்.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 1, 1, 1, 0, NULL, 0, 'manual', NULL, NULL, NULL, 1, 0, NULL, NULL, 12520, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 11:38:16', NULL, '2026-04-20 06:08:16', '2026-05-06 14:38:44', NULL, NULL, NULL),
(41, 2, NULL, 4, NULL, NULL, 'ரஜினிகாந்த் புதிய திரைப்படம் ஜனவரியில் வெளியாகிறது', 'rajinikanth-new-movie-releasing-january', 'சூப்பர்ஸ்டார் ரஜினிகாந்தின் புதிய திரைப்படம் வரும் ஜனவரி மாதம் திரையரங்குகளில் வெளியாகவுள்ளது.', NULL, '<p>சூப்பர்ஸ்டார் ரஜினிகாந்தின் புதிய திரைப்படம் வரும் ஜனவரி 14 ஆம் தேதி பொங்கல் சீஸனில் வெளியாகவுள்ளது என்று தயாரிப்பாளர்கள் அறிவித்துள்ளனர்.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, 0, 'manual', NULL, NULL, NULL, 2, 0, NULL, NULL, 8905, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 06:38:16', NULL, '2026-04-20 01:08:16', '2026-04-20 09:31:59', NULL, NULL, NULL),
(42, 2, NULL, 4, NULL, NULL, 'Netflix-ல் வரும் தமிழ் படங்கள் — டிசம்பர் முழு பட்டியல்', 'netflix-tamil-movies-december-full-list', 'டிசம்பர் மாதம் Netflix-ல் வரும் தமிழ் படங்கள் மற்றும் வெப் சீரீஸ்களின் முழுமையான பட்டியல்.', NULL, '<p>டிசம்பர் மாதம் Netflix-ல் வெளியாகவுள்ள தமிழ் படங்கள் மற்றும் வெப் சீரீஸ்களின் பட்டியல் வெளியிடப்பட்டுள்ளது.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, 0, 'manual', NULL, NULL, NULL, 2, 0, NULL, NULL, 4200, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 02:38:16', NULL, '2026-04-19 21:08:16', '2026-04-20 07:08:16', NULL, NULL, NULL),
(43, 2, NULL, 5, NULL, NULL, 'IPL 2025 — சென்னை சூப்பர் கிங்ஸ் புதிய வீரர்கள் அறிவிப்பு', 'ipl-2025-chennai-super-kings-new-players-announcement', 'IPL 2025 ஏலத்தில் சென்னை சூப்பர் கிங்ஸ் அணி பலத்த வீரர்களை வாங்கியுள்ளது.', NULL, '<p>IPL 2025 சீஸனுக்கான வீரர் ஏலம் நடைபெற்றது. சென்னை சூப்பர் கிங்ஸ் அணி பலமான வீரர்களை தங்கள் அணியில் இணைத்துக்கொண்டுள்ளது.</p><p>MS Dhoni இந்த ஆண்டும் அணியை வழிநடத்துவார்.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 0, 1, 0, 0, NULL, 0, 'manual', NULL, NULL, NULL, 2, 0, NULL, NULL, 7805, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 08:38:16', NULL, '2026-04-20 03:08:16', '2026-04-29 07:38:12', NULL, NULL, NULL),
(44, 2, NULL, 5, NULL, NULL, 'விஸ்வநாதன் ஆனந்த் உலக சதுரங்க போட்டியில் வெற்றி', 'viswanathan-anand-world-chess-championship-victory', 'இந்தியாவின் சதுரங்க வீரர் விஸ்வநாதன் ஆனந்த் உலக சதுரங்க போட்டியில் சாம்பியனாகியுள்ளார்.', NULL, '<p>இந்தியாவின் சதுரங்க மேதை விஸ்வநாதன் ஆனந்த் உலக சதுரங்க சாம்பியன்ஷிப் போட்டியில் வெற்றி பெற்றுள்ளார்.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, 0, 'manual', NULL, NULL, NULL, 2, 0, NULL, NULL, 3200, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 05:38:16', NULL, '2026-04-20 00:08:16', '2026-04-20 07:08:16', NULL, NULL, NULL),
(45, 2, NULL, 6, NULL, NULL, 'AI-யால் மருத்துவ துறையில் புரட்சி — இந்தியாவில் புதிய கண்டுபிடிப்பு', 'ai-revolution-medical-field-india-new-discovery', 'செயற்கை நுண்ணறிவு தொழில்நுட்பம் மருத்துவ துறையில் புரட்சியை ஏற்படுத்தி வருகிறது.', NULL, '<p>இந்திய விஞ்ஞானிகள் செயற்கை நுண்ணறிவு தொழில்நுட்பத்தை பயன்படுத்தி புற்றுநோயை ஆரம்ப நிலையிலேயே கண்டறியும் முறையை உருவாக்கியுள்ளனர்.</p>', NULL, 'news', 'ta', NULL, NULL, 'published', 'reporter', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, 0, 'manual', NULL, NULL, NULL, 2, 0, NULL, NULL, 2800, 0, 0.00, 0, NULL, NULL, 'NewsArticle', NULL, NULL, NULL, '2026-04-20 03:38:16', NULL, '2026-04-19 22:08:16', '2026-04-20 07:08:16', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tn_article_ratings`
--

CREATE TABLE `tn_article_ratings` (
  `id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `reader_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL COMMENT '1-5',
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_article_tags`
--

CREATE TABLE `tn_article_tags` (
  `article_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_article_tags`
--

INSERT INTO `tn_article_tags` (`article_id`, `tag_id`) VALUES
(35, 1),
(35, 13),
(36, 2),
(37, 1),
(37, 14),
(38, 9),
(38, 14),
(40, 8),
(41, 7),
(43, 5),
(43, 6);

-- --------------------------------------------------------

--
-- Table structure for table `tn_categories`
--

CREATE TABLE `tn_categories` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `parent_id` smallint(5) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `name_tamil` varchar(100) DEFAULT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_categories`
--

INSERT INTO `tn_categories` (`id`, `parent_id`, `name`, `name_tamil`, `slug`, `description`, `image`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Tamil Nadu', 'தமிழ்நாடு', 'tamil-nadu', NULL, NULL, 1, 1, '2026-04-20 06:36:28', '2026-04-20 06:36:28'),
(2, NULL, 'India', 'இந்தியா', 'india', NULL, NULL, 2, 1, '2026-04-20 06:36:28', '2026-04-20 06:36:28'),
(3, NULL, 'World', 'உலகம்', 'world', NULL, NULL, 3, 1, '2026-04-20 06:36:28', '2026-04-20 06:36:28'),
(4, NULL, 'Cinema', 'சினிமா', 'cinema', NULL, NULL, 4, 1, '2026-04-20 06:36:28', '2026-04-20 06:36:28'),
(5, NULL, 'Sports', 'விளையாட்டு', 'sports', NULL, NULL, 5, 1, '2026-04-20 06:36:28', '2026-04-20 06:36:28'),
(6, NULL, 'Technology', 'தொழில்நுட்பம்', 'technology', NULL, NULL, 6, 1, '2026-04-20 06:36:28', '2026-04-20 06:36:28'),
(7, NULL, 'Spiritual', 'ஆன்மீகம்', 'spiritual', NULL, NULL, 7, 1, '2026-04-20 06:36:28', '2026-04-20 06:36:28'),
(8, NULL, 'Jobs & Education', 'வேலை & கல்வி', 'jobs-education', NULL, NULL, 8, 1, '2026-04-20 06:36:28', '2026-04-20 06:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `tn_cities`
--

CREATE TABLE `tn_cities` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `district_id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_cities`
--

INSERT INTO `tn_cities` (`id`, `district_id`, `name`, `slug`, `is_active`) VALUES
(1, 1, 'Chennai', 'chennai', 1),
(2, 2, 'Madurai', 'madurai', 1),
(3, 3, 'Coimbatore', 'coimbatore', 1),
(4, 4, 'Salem', 'salem', 1),
(5, 5, 'Trichy', 'trichy', 1),
(6, 6, 'Tirunelveli', 'tirunelveli', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tn_contributors`
--

CREATE TABLE `tn_contributors` (
  `id` int(10) UNSIGNED NOT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_contributors`
--

INSERT INTO `tn_contributors` (`id`, `google_id`, `name`, `email`, `avatar`, `bio`, `is_active`, `is_blocked`, `last_login`, `created_at`, `updated_at`) VALUES
(1, NULL, 'kannan', 'sa.kannan25@gmail.com', NULL, NULL, 1, 0, NULL, '2026-04-25 08:15:14', '2026-04-25 08:15:14');

-- --------------------------------------------------------

--
-- Table structure for table `tn_contributor_categories`
--

CREATE TABLE `tn_contributor_categories` (
  `contributor_id` int(10) UNSIGNED NOT NULL,
  `category_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_contributor_categories`
--

INSERT INTO `tn_contributor_categories` (`contributor_id`, `category_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tn_cron_logs`
--

CREATE TABLE `tn_cron_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `job` varchar(100) NOT NULL,
  `status` enum('success','error') NOT NULL DEFAULT 'success',
  `message` text DEFAULT NULL,
  `records` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `duration_ms` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ran_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_csrf_tokens`
--

CREATE TABLE `tn_csrf_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_districts`
--

CREATE TABLE `tn_districts` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `state_id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_districts`
--

INSERT INTO `tn_districts` (`id`, `state_id`, `name`, `slug`, `is_active`) VALUES
(1, 1, 'Chennai', 'chennai-district', 1),
(2, 1, 'Madurai', 'madurai-district', 1),
(3, 1, 'Coimbatore', 'coimbatore-district', 1),
(4, 1, 'Salem', 'salem-district', 1),
(5, 1, 'Trichy', 'trichy-district', 1),
(6, 1, 'Tirunelveli', 'tirunelveli-district', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tn_editor_permissions`
--

CREATE TABLE `tn_editor_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `perm_type` enum('district','category','state') NOT NULL DEFAULT 'district',
  `ref_id` smallint(5) UNSIGNED NOT NULL COMMENT 'district_id or category_id or state_id',
  `can_approve` tinyint(1) NOT NULL DEFAULT 1,
  `can_publish` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'publish without chief editor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_editor_permissions`
--

INSERT INTO `tn_editor_permissions` (`id`, `user_id`, `perm_type`, `ref_id`, `can_approve`, `can_publish`, `created_at`) VALUES
(1, 10, 'district', 1, 1, 0, '2026-04-27 08:09:41');

-- --------------------------------------------------------

--
-- Table structure for table `tn_fcm_topics`
--

CREATE TABLE `tn_fcm_topics` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_fcm_topics`
--

INSERT INTO `tn_fcm_topics` (`id`, `name`, `slug`, `is_active`) VALUES
(1, 'General News', 'general', 1),
(2, 'Breaking News', 'breaking', 1),
(3, 'Cinema', 'cinema', 1),
(4, 'Sports', 'sports', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tn_linking_config`
--

CREATE TABLE `tn_linking_config` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `match_by` set('category','tags','location') NOT NULL DEFAULT 'category,tags',
  `count` tinyint(3) UNSIGNED NOT NULL DEFAULT 4,
  `allow_override` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_linking_config`
--

INSERT INTO `tn_linking_config` (`id`, `match_by`, `count`, `allow_override`, `updated_at`) VALUES
(1, 'category,tags', 4, 1, '2026-04-20 06:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `tn_live_blogs`
--

CREATE TABLE `tn_live_blogs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(300) NOT NULL,
  `slug` varchar(320) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('general','election','cricket','football','sports','disaster','budget') NOT NULL DEFAULT 'general',
  `team_home` varchar(100) DEFAULT NULL,
  `team_away` varchar(100) DEFAULT NULL,
  `score_home` varchar(50) DEFAULT NULL,
  `score_away` varchar(50) DEFAULT NULL,
  `status` enum('active','ended') NOT NULL DEFAULT 'active',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_live_entries`
--

CREATE TABLE `tn_live_entries` (
  `id` int(10) UNSIGNED NOT NULL,
  `live_blog_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `label` varchar(50) DEFAULT NULL,
  `label_color` varchar(20) NOT NULL DEFAULT '#C0001A',
  `score_home` varchar(50) DEFAULT NULL,
  `score_away` varchar(50) DEFAULT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `image_url` varchar(500) DEFAULT NULL,
  `youtube_video_id` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_media`
--

CREATE TABLE `tn_media` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `thumb_path` varchar(500) DEFAULT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `width` smallint(5) UNSIGNED DEFAULT NULL,
  `height` smallint(5) UNSIGNED DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` varchar(300) DEFAULT NULL,
  `photo_credit` varchar(150) DEFAULT NULL,
  `folder` varchar(100) NOT NULL DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_newspapers`
--

CREATE TABLE `tn_newspapers` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `title_tamil` varchar(200) DEFAULT NULL,
  `edition_date` date NOT NULL COMMENT 'The paper date (daily/weekly)',
  `edition_type` enum('daily','weekly','special') NOT NULL DEFAULT 'daily',
  `pdf_path` varchar(500) NOT NULL,
  `thumb_path` varchar(500) DEFAULT NULL,
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'bytes',
  `pages` tinyint(3) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `download_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_notifications`
--

CREATE TABLE `tn_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'recipient',
  `from_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'sender/actor',
  `type` enum('article_submitted','article_approved','article_rejected','article_published','auto_published','edit_submitted','edit_approved','edit_rejected','escalated') NOT NULL,
  `article_id` int(10) UNSIGNED DEFAULT NULL,
  `message` varchar(300) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_notifications`
--

INSERT INTO `tn_notifications` (`id`, `user_id`, `from_id`, `type`, `article_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 3, 'edit_submitted', 35, 'Kannan submitted an edit for: \"தமிழ்நாட்டில் புதிய மழை எச்சரிக்கை — வடகிழக்கு பருவமழை தீவிரமடைகிறது\"', 0, '2026-04-28 03:41:40'),
(2, 3, 3, 'edit_submitted', 35, 'Kannan submitted an edit for: \"தமிழ்நாட்டில் புதிய மழை எச்சரிக்கை — வடகிழக்கு பருவமழை தீவிரமடைகிறது\"', 1, '2026-04-28 03:41:40'),
(3, 2, 3, 'edit_submitted', 35, 'Kannan submitted an edit for: \"தமிழ்நாட்டில் புதிய மழை எச்சரிக்கை — வடகிழக்கு பருவமழை தீவிரமடைகிறது\"', 0, '2026-04-28 03:42:00'),
(4, 3, 3, 'edit_submitted', 35, 'Kannan submitted an edit for: \"தமிழ்நாட்டில் புதிய மழை எச்சரிக்கை — வடகிழக்கு பருவமழை தீவிரமடைகிறது\"', 1, '2026-04-28 03:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `tn_premium_access`
--

CREATE TABLE `tn_premium_access` (
  `id` int(10) UNSIGNED NOT NULL,
  `reader_id` int(10) UNSIGNED NOT NULL,
  `plan_id` tinyint(3) UNSIGNED NOT NULL,
  `starts_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `status` enum('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `payment_ref` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_premium_plans`
--

CREATE TABLE `tn_premium_plans` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_tamil` varchar(100) DEFAULT NULL,
  `price_inr` decimal(8,2) NOT NULL DEFAULT 0.00,
  `duration_days` smallint(5) UNSIGNED NOT NULL DEFAULT 30,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_premium_plans`
--

INSERT INTO `tn_premium_plans` (`id`, `name`, `name_tamil`, `price_inr`, `duration_days`, `is_active`, `created_at`) VALUES
(1, 'Monthly', 'மாதாந்திர', 99.00, 30, 0, '2026-04-20 06:36:28'),
(2, 'Annual', 'ஆண்டு', 799.00, 365, 0, '2026-04-20 06:36:28'),
(3, 'Monthly', 'மாதாந்திர', 99.00, 30, 0, '2026-04-25 08:04:15'),
(4, 'Annual', 'ஆண்டு', 799.00, 365, 0, '2026-04-25 08:04:15');

-- --------------------------------------------------------

--
-- Table structure for table `tn_print_editions`
--

CREATE TABLE `tn_print_editions` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `edition_date` date NOT NULL,
  `status` enum('draft','ready','printed') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_print_editions`
--

INSERT INTO `tn_print_editions` (`id`, `title`, `edition_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'வேள் சுடர் — 05 May 2026', '2026-05-05', 'ready', NULL, 2, '2026-05-05 14:47:37', '2026-05-05 14:48:37');

-- --------------------------------------------------------

--
-- Table structure for table `tn_print_edition_articles`
--

CREATE TABLE `tn_print_edition_articles` (
  `edition_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_print_edition_articles`
--

INSERT INTO `tn_print_edition_articles` (`edition_id`, `article_id`, `sort_order`, `added_at`) VALUES
(1, 36, 6, '2026-05-05 15:02:05'),
(1, 39, 4, '2026-05-05 14:47:47'),
(1, 40, 1, '2026-05-05 14:47:42'),
(1, 42, 5, '2026-05-05 15:02:03'),
(1, 43, 2, '2026-05-05 14:47:44');

-- --------------------------------------------------------

--
-- Table structure for table `tn_push_notifications`
--

CREATE TABLE `tn_push_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `topic_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_readers`
--

CREATE TABLE `tn_readers` (
  `id` int(10) UNSIGNED NOT NULL,
  `google_id` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_reporter_assignments`
--

CREATE TABLE `tn_reporter_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `reporter_id` int(10) UNSIGNED NOT NULL,
  `district_editor_id` int(10) UNSIGNED NOT NULL,
  `assigned_by` int(10) UNSIGNED NOT NULL COMMENT 'chief_editor user id',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_roles`
--

CREATE TABLE `tn_roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_roles`
--

INSERT INTO `tn_roles` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Admin', 'admin', '2026-04-20 06:36:28'),
(2, 'Editor', 'editor', '2026-04-20 06:36:28'),
(3, 'Reporter', 'reporter', '2026-04-20 06:36:28'),
(4, 'District Editor', 'district_editor', '2026-04-27 08:09:41'),
(5, 'Category Editor', 'category_editor', '2026-04-27 08:09:41'),
(7, 'Chief Editor', 'chief_editor', '2026-04-27 09:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `tn_rss_feeds`
--

CREATE TABLE `tn_rss_feeds` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `url` varchar(500) NOT NULL,
  `category_id` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `fetch_interval` smallint(5) UNSIGNED NOT NULL DEFAULT 30,
  `last_fetched_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_rss_imports`
--

CREATE TABLE `tn_rss_imports` (
  `id` int(10) UNSIGNED NOT NULL,
  `feed_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(500) NOT NULL,
  `source_url` varchar(500) NOT NULL,
  `source_hash` varchar(64) NOT NULL,
  `status` enum('pending','imported','skipped') NOT NULL DEFAULT 'pending',
  `fetched_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_seo_config`
--

CREATE TABLE `tn_seo_config` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `page_type` varchar(50) NOT NULL,
  `meta_title` varchar(300) DEFAULT NULL,
  `meta_desc` varchar(500) DEFAULT NULL,
  `og_image` varchar(500) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_seo_config`
--

INSERT INTO `tn_seo_config` (`id`, `page_type`, `meta_title`, `meta_desc`, `og_image`, `updated_at`) VALUES
(1, 'homepage', 'Tamil News Portal | Latest Tamil News', 'Read the latest Tamil news, breaking news, cinema, sports, and more.', NULL, '2026-04-20 06:36:28'),
(2, 'category', NULL, NULL, NULL, '2026-04-20 06:36:28'),
(3, 'article', NULL, NULL, NULL, '2026-04-20 06:36:28'),
(4, 'video', NULL, NULL, NULL, '2026-04-20 06:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `tn_sessions`
--

CREATE TABLE `tn_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_settings`
--

CREATE TABLE `tn_settings` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `label` varchar(200) DEFAULT NULL,
  `input_type` varchar(30) NOT NULL DEFAULT 'text',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_settings`
--

INSERT INTO `tn_settings` (`id`, `group`, `key`, `value`, `label`, `input_type`, `updated_at`) VALUES
(1, 'general', 'site_name', 'தமிழ் செய்தி', 'Site Name', 'text', '2026-04-20 07:03:15'),
(2, 'general', 'site_url', 'http://localhost/tamilnews', 'Site URL', 'text', '2026-04-20 07:03:15'),
(3, 'general', 'site_tagline', 'Latest Tamil News', 'Site Tagline', 'text', '2026-04-20 06:36:28'),
(4, 'general', 'site_logo', NULL, 'Site Logo URL', 'image', '2026-04-20 06:36:28'),
(5, 'general', 'contact_email', NULL, 'Contact Email', 'email', '2026-04-20 06:36:28'),
(6, 'general', 'articles_per_page', '12', 'Articles Per Page', 'number', '2026-04-20 06:36:28'),
(7, 'breaking', 'ticker_enabled', '1', 'Ticker Enabled', 'toggle', '2026-04-20 06:36:28'),
(8, 'breaking', 'ticker_speed', '50', 'Ticker Speed', 'number', '2026-04-20 06:36:28'),
(9, 'breaking', 'breaking_expiry_hours', '6', 'Breaking Expiry (hrs)', 'number', '2026-04-20 06:36:28'),
(10, 'youtube', 'api_key', NULL, 'YouTube API Key', 'text', '2026-04-20 06:36:28'),
(11, 'youtube', 'fetch_interval', 'hourly', 'Fetch Interval', 'select', '2026-04-20 06:36:28'),
(12, 'youtube', 'default_auto_publish', '0', 'Auto-publish by default', 'toggle', '2026-04-20 06:36:28'),
(13, 'rss', 'fetch_interval_min', '30', 'RSS Fetch Interval (min)', 'number', '2026-04-20 06:36:28'),
(14, 'fcm', 'server_key', NULL, 'FCM Server Key', 'text', '2026-04-20 06:36:28'),
(15, 'fcm', 'sender_id', NULL, 'FCM Sender ID', 'text', '2026-04-20 06:36:28'),
(16, 'social', 'facebook_url', NULL, 'Facebook Page URL', 'text', '2026-04-20 06:36:28'),
(17, 'social', 'twitter_url', NULL, 'Twitter/X URL', 'text', '2026-04-20 06:36:28'),
(18, 'social', 'youtube_url', NULL, 'YouTube Channel URL', 'text', '2026-04-20 06:36:28'),
(19, 'social', 'whatsapp_number', NULL, 'WhatsApp Number', 'text', '2026-04-20 06:36:28'),
(20, 'cache', 'homepage_cache_ttl', '300', 'Homepage Cache TTL (sec)', 'number', '2026-04-20 06:36:28'),
(21, 'cache', 'category_cache_ttl', '300', 'Category Cache TTL (sec)', 'number', '2026-04-20 06:36:28'),
(22, 'seo', 'google_analytics_id', NULL, 'GA Measurement ID', 'text', '2026-04-20 06:36:28'),
(23, 'seo', 'google_news_enabled', '1', 'Google News Sitemap', 'toggle', '2026-04-20 06:36:28'),
(24, 'admin', 'ip_whitelist', NULL, 'Admin IP Whitelist', 'textarea', '2026-04-20 06:36:28'),
(25, 'admin', 'related_articles_count', '4', 'Related Articles Count', 'number', '2026-04-20 06:36:28'),
(26, 'oauth', 'google_client_id', NULL, 'Google OAuth Client ID', 'text', '2026-04-20 06:36:28'),
(27, 'oauth', 'google_client_secret', NULL, 'Google OAuth Client Secret', 'text', '2026-04-20 06:36:28'),
(28, 'oauth', 'contributor_portal', '1', 'Contributor Portal Enabled', 'toggle', '2026-04-20 06:36:28'),
(29, 'oauth', 'reader_login', '1', 'Reader Login Enabled', 'toggle', '2026-04-20 06:36:28'),
(30, 'general', 'portal_enabled', '1', 'Editor/Reporter Portal Enabled', 'toggle', '2026-04-24 13:43:23'),
(31, 'general', 'portal_url', '/portal/dashboard', 'Portal URL', 'text', '2026-04-24 13:43:23'),
(32, 'admin', 'live_blog_api_key', NULL, 'Live Blog API Key (for external integrations)', 'text', '2026-04-25 08:33:27');

-- --------------------------------------------------------

--
-- Table structure for table `tn_sitemap_log`
--

CREATE TABLE `tn_sitemap_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` enum('news','standard') NOT NULL DEFAULT 'news',
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `url_count` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_special_categories`
--

CREATE TABLE `tn_special_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `name_tamil` varchar(150) DEFAULT NULL,
  `slug` varchar(160) NOT NULL,
  `type` enum('election','festival','event','disaster','sports','budget') NOT NULL DEFAULT 'event',
  `description` text DEFAULT NULL,
  `banner_color` varchar(20) NOT NULL DEFAULT '#C0001A',
  `banner_icon` varchar(10) DEFAULT NULL,
  `category_id` smallint(5) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_special_categories`
--

INSERT INTO `tn_special_categories` (`id`, `name`, `name_tamil`, `slug`, `type`, `description`, `banner_color`, `banner_icon`, `category_id`, `is_active`, `is_pinned`, `sort_order`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
(1, 'Tamil Nadu Elections 2026', 'தமிழ்நாடு தேர்தல் 2026', 'tn-elections-2026', 'election', 'Complete coverage of Tamil Nadu Assembly Elections 2026', '#1877F2', '🗳️', NULL, 0, 0, 0, NULL, NULL, '2026-04-24 13:43:23', '2026-04-24 13:43:23'),
(2, 'Pongal 2025', 'பொங்கல் 2025', 'pongal-2025', 'festival', 'Pongal festival news and celebrations', '#E8A000', '🎉', NULL, 1, 1, 0, NULL, NULL, '2026-04-24 13:43:23', '2026-04-24 13:43:23'),
(3, 'IPL 2025', 'ஐபிஎல் 2025', 'ipl-2025', 'sports', 'IPL 2025 season complete coverage', '#1B6B2E', '🏏', NULL, 1, 1, 0, NULL, NULL, '2026-04-24 13:43:23', '2026-04-24 13:43:23'),
(4, 'Union Budget 2025', 'மத்திய பட்ஜெட் 2025', 'union-budget-2025', 'budget', 'Union Budget 2025 analysis and reactions', '#7F77DD', '💰', NULL, 1, 0, 0, NULL, NULL, '2026-04-24 13:43:23', '2026-04-24 13:43:23');

-- --------------------------------------------------------

--
-- Table structure for table `tn_special_category_articles`
--

CREATE TABLE `tn_special_category_articles` (
  `special_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_states`
--

CREATE TABLE `tn_states` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_states`
--

INSERT INTO `tn_states` (`id`, `name`, `slug`, `is_active`) VALUES
(1, 'Tamil Nadu', 'tamil-nadu', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tn_tags`
--

CREATE TABLE `tn_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_tamil` varchar(100) DEFAULT NULL,
  `slug` varchar(120) NOT NULL,
  `usage_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_tags`
--

INSERT INTO `tn_tags` (`id`, `name`, `name_tamil`, `slug`, `usage_count`, `created_at`) VALUES
(1, 'Tamil Nadu', 'தமிழ்நாடு', 'tamil-nadu-tag', 2, '2026-04-20 06:58:15'),
(2, 'Chennai', 'சென்னை', 'chennai', 1, '2026-04-20 06:58:15'),
(3, 'DMK', 'திமுக', 'dmk', 0, '2026-04-20 06:58:15'),
(4, 'ADMK', 'அதிமுக', 'admk', 0, '2026-04-20 06:58:15'),
(5, 'Cricket', 'கிரிக்கெட்', 'cricket', 1, '2026-04-20 06:58:15'),
(6, 'IPL', 'ஐபிஎல்', 'ipl', 1, '2026-04-20 06:58:15'),
(7, 'Rajinikanth', 'ரஜினிகாந்த்', 'rajinikanth', 1, '2026-04-20 06:58:15'),
(8, 'Vijay', 'விஜய்', 'vijay', 1, '2026-04-20 06:58:15'),
(9, 'Politics', 'அரசியல்', 'politics', 1, '2026-04-20 06:58:15'),
(10, 'Education', 'கல்வி', 'education', 0, '2026-04-20 06:58:15'),
(11, 'Health', 'சுகாதாரம்', 'health', 0, '2026-04-20 06:58:15'),
(12, 'Technology', 'தொழில்நுட்பம்', 'technology-tag', 0, '2026-04-20 06:58:15'),
(13, 'Weather', 'வானிலை', 'weather', 1, '2026-04-20 06:58:15'),
(14, 'Economy', 'பொருளாதாரம்', 'economy', 2, '2026-04-20 06:58:15'),
(15, 'Sports', 'விளையாட்டு', 'sports-tag', 0, '2026-04-20 06:58:15');

-- --------------------------------------------------------

--
-- Table structure for table `tn_users`
--

CREATE TABLE `tn_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_district_id` smallint(5) UNSIGNED DEFAULT NULL,
  `assigned_category_ids` varchar(200) DEFAULT NULL,
  `auto_approve` tinyint(1) NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_users`
--

INSERT INTO `tn_users` (`id`, `role_id`, `name`, `email`, `password`, `avatar`, `is_active`, `is_blocked`, `assigned_district_id`, `assigned_category_ids`, `auto_approve`, `last_login`, `created_at`, `updated_at`) VALUES
(2, 1, 'Admin User', 'admin@tamilnews.com', '$2y$10$D.cZmo/ng0Cpnekl0zjuuOSLJHLjRDbVHiZGay/OHy9mRxL55RkhG', NULL, 1, 0, NULL, NULL, 0, '2026-05-06 14:55:38', '2026-04-20 06:58:15', '2026-05-06 09:25:38'),
(3, 2, 'Kannan', 'editor@tamilnews.com', '$2y$10$fegkv3W4igebrh9rgU6jeeB4n8jCEZNfty8lz/CVNAYMSyWScsWxa', NULL, 1, 0, NULL, NULL, 0, '2026-05-06 14:51:50', '2026-04-20 06:58:15', '2026-05-06 09:21:50'),
(4, 3, 'Reporter', 'reporter@tamilnews.com', '$2y$10$fegkv3W4igebrh9rgU6jeeB4n8jCEZNfty8lz/CVNAYMSyWScsWxa', NULL, 1, 0, NULL, NULL, 0, '2026-05-06 14:51:34', '2026-04-20 06:58:15', '2026-05-06 09:21:34'),
(10, 4, 'Chennai District Editor', 'chennai.editor@tamilnews.com', '$2y$12$LJn8MiRg9a8wQE5YeLq7FON0EGWXk1u6HiHwb68hj8BvwfPX6K5Ca', NULL, 1, 0, 1, NULL, 0, NULL, '2026-04-27 08:09:41', '2026-04-27 08:09:41'),
(11, 7, 'Chief Editor', 'chiefeditor@tamilnews.com', '$2y$12$LJn8MiRg9a8wQE5YeLq7FON0EGWXk1u6HiHwb68hj8BvwfPX6K5Ca', NULL, 1, 0, NULL, NULL, 0, NULL, '2026-04-27 09:35:44', '2026-04-27 09:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `tn_user_badges`
--

CREATE TABLE `tn_user_badges` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `name_tamil` varchar(80) DEFAULT NULL,
  `icon` varchar(10) NOT NULL DEFAULT '?',
  `color` varchar(20) NOT NULL DEFAULT '#E8A000',
  `description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tn_user_badges`
--

INSERT INTO `tn_user_badges` (`id`, `name`, `name_tamil`, `icon`, `color`, `description`, `created_at`) VALUES
(1, 'Top Reporter', 'சிறந்த நிருபர்', '🏆', '#E8A000', 'Awarded for consistent quality reporting', '2026-04-25 08:33:27'),
(2, 'Breaking News', 'உடனடி செய்தி', '⚡', '#C0001A', 'Fastest to break important stories', '2026-04-25 08:33:27'),
(3, 'Verified Writer', 'சரிபார்க்கப்பட்டவர்', '✅', '#1B6B2E', 'Identity and credentials verified', '2026-04-25 08:33:27'),
(4, 'Star Contributor', 'நட்சத்திர எழுத்தாளர்', '⭐', '#1877F2', '50+ published articles', '2026-04-25 08:33:27'),
(5, 'Editor Pick', 'ஆசிரியர் தேர்வு', '📌', '#7F77DD', 'Consistently featured by editors', '2026-04-25 08:33:27');

-- --------------------------------------------------------

--
-- Table structure for table `tn_user_badge_assignments`
--

CREATE TABLE `tn_user_badge_assignments` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `badge_id` tinyint(3) UNSIGNED NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_youtube_channels`
--

CREATE TABLE `tn_youtube_channels` (
  `id` int(10) UNSIGNED NOT NULL,
  `channel_id` varchar(100) NOT NULL,
  `channel_name` varchar(200) NOT NULL,
  `playlist_id` varchar(100) DEFAULT NULL,
  `category_id` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `auto_publish` tinyint(1) NOT NULL DEFAULT 0,
  `fetch_interval` enum('hourly','daily') NOT NULL DEFAULT 'hourly',
  `last_fetched_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_youtube_imports`
--

CREATE TABLE `tn_youtube_imports` (
  `id` int(10) UNSIGNED NOT NULL,
  `channel_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED DEFAULT NULL,
  `video_id` varchar(20) NOT NULL,
  `title` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `status` enum('pending','imported','skipped') NOT NULL DEFAULT 'pending',
  `imported_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tn_youtube_keyword_map`
--

CREATE TABLE `tn_youtube_keyword_map` (
  `id` int(10) UNSIGNED NOT NULL,
  `channel_id` int(10) UNSIGNED NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `category_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tn_activity_log`
--
ALTER TABLE `tn_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_al_user` (`user_id`),
  ADD KEY `idx_al_entity` (`entity`,`entity_id`);

--
-- Indexes for table `tn_ad_slots`
--
ALTER TABLE `tn_ad_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ad_slug` (`slug`);

--
-- Indexes for table `tn_analytics_daily`
--
ALTER TABLE `tn_analytics_daily`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_analytics_art_date` (`article_id`,`date`),
  ADD KEY `idx_analytics_date` (`date`);

--
-- Indexes for table `tn_articles`
--
ALTER TABLE `tn_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_article_slug` (`slug`),
  ADD KEY `idx_article_status` (`status`),
  ADD KEY `idx_article_breaking` (`is_breaking`),
  ADD KEY `idx_article_featured` (`is_featured`),
  ADD KEY `idx_article_published` (`published_at`),
  ADD KEY `idx_article_category` (`category_id`),
  ADD KEY `fk_article_user` (`user_id`),
  ADD KEY `fk_article_contributor` (`contributor_id`),
  ADD KEY `fk_article_city` (`city_id`),
  ADD KEY `fk_article_media` (`media_id`),
  ADD KEY `idx_source_hash` (`source_hash`),
  ADD KEY `idx_article_series` (`series_id`);

--
-- Indexes for table `tn_article_ratings`
--
ALTER TABLE `tn_article_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rating_article_reader` (`article_id`,`reader_id`),
  ADD KEY `fk_rating_article` (`article_id`),
  ADD KEY `fk_rating_reader` (`reader_id`);

--
-- Indexes for table `tn_article_tags`
--
ALTER TABLE `tn_article_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `fk_at_tag` (`tag_id`);

--
-- Indexes for table `tn_categories`
--
ALTER TABLE `tn_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cat_slug` (`slug`),
  ADD KEY `fk_cat_parent` (`parent_id`);

--
-- Indexes for table `tn_cities`
--
ALTER TABLE `tn_cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_city_slug` (`slug`),
  ADD KEY `fk_city_dist` (`district_id`);

--
-- Indexes for table `tn_contributors`
--
ALTER TABLE `tn_contributors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contributor_email` (`email`),
  ADD UNIQUE KEY `uq_contributor_google_id` (`google_id`);

--
-- Indexes for table `tn_contributor_categories`
--
ALTER TABLE `tn_contributor_categories`
  ADD PRIMARY KEY (`contributor_id`,`category_id`),
  ADD KEY `fk_cc_category` (`category_id`);

--
-- Indexes for table `tn_cron_logs`
--
ALTER TABLE `tn_cron_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cron_job` (`job`),
  ADD KEY `idx_cron_ran` (`ran_at`);

--
-- Indexes for table `tn_csrf_tokens`
--
ALTER TABLE `tn_csrf_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_csrf_token` (`token`),
  ADD KEY `fk_csrf_user` (`user_id`);

--
-- Indexes for table `tn_districts`
--
ALTER TABLE `tn_districts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_dist_slug` (`slug`),
  ADD KEY `fk_dist_state` (`state_id`);

--
-- Indexes for table `tn_editor_permissions`
--
ALTER TABLE `tn_editor_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_editor_perm` (`user_id`,`perm_type`,`ref_id`),
  ADD KEY `fk_ep_user` (`user_id`);

--
-- Indexes for table `tn_fcm_topics`
--
ALTER TABLE `tn_fcm_topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_fcm_slug` (`slug`);

--
-- Indexes for table `tn_linking_config`
--
ALTER TABLE `tn_linking_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tn_live_blogs`
--
ALTER TABLE `tn_live_blogs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_live_slug` (`slug`),
  ADD KEY `fk_lb_user` (`user_id`),
  ADD KEY `fk_lb_article` (`article_id`),
  ADD KEY `idx_lb_status` (`status`);

--
-- Indexes for table `tn_live_entries`
--
ALTER TABLE `tn_live_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_le_blog` (`live_blog_id`),
  ADD KEY `fk_le_user` (`user_id`),
  ADD KEY `idx_le_created` (`live_blog_id`,`created_at`);

--
-- Indexes for table `tn_media`
--
ALTER TABLE `tn_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_media_user` (`user_id`);

--
-- Indexes for table `tn_newspapers`
--
ALTER TABLE `tn_newspapers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_edition_date` (`edition_date`,`edition_type`),
  ADD KEY `fk_np_user` (`uploaded_by`),
  ADD KEY `idx_np_date` (`edition_date`);

--
-- Indexes for table `tn_notifications`
--
ALTER TABLE `tn_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user` (`user_id`,`is_read`),
  ADD KEY `fk_notif_article` (`article_id`);

--
-- Indexes for table `tn_premium_access`
--
ALTER TABLE `tn_premium_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pa_reader` (`reader_id`),
  ADD KEY `fk_pa_plan` (`plan_id`),
  ADD KEY `idx_pa_expires` (`expires_at`);

--
-- Indexes for table `tn_premium_plans`
--
ALTER TABLE `tn_premium_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tn_print_editions`
--
ALTER TABLE `tn_print_editions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pe_user` (`created_by`);

--
-- Indexes for table `tn_print_edition_articles`
--
ALTER TABLE `tn_print_edition_articles`
  ADD PRIMARY KEY (`edition_id`,`article_id`),
  ADD KEY `fk_pea_article` (`article_id`);

--
-- Indexes for table `tn_push_notifications`
--
ALTER TABLE `tn_push_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pn_user` (`user_id`),
  ADD KEY `fk_pn_topic` (`topic_id`);

--
-- Indexes for table `tn_readers`
--
ALTER TABLE `tn_readers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_reader_google_id` (`google_id`),
  ADD UNIQUE KEY `uq_reader_email` (`email`);

--
-- Indexes for table `tn_reporter_assignments`
--
ALTER TABLE `tn_reporter_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_reporter` (`reporter_id`),
  ADD KEY `fk_ra_editor` (`district_editor_id`),
  ADD KEY `fk_ra_assigner` (`assigned_by`);

--
-- Indexes for table `tn_roles`
--
ALTER TABLE `tn_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_role_slug` (`slug`);

--
-- Indexes for table `tn_rss_feeds`
--
ALTER TABLE `tn_rss_feeds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tn_rss_imports`
--
ALTER TABLE `tn_rss_imports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rss_hash` (`source_hash`),
  ADD KEY `fk_ri_feed` (`feed_id`),
  ADD KEY `fk_ri_article` (`article_id`);

--
-- Indexes for table `tn_seo_config`
--
ALTER TABLE `tn_seo_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_seo_page` (`page_type`);

--
-- Indexes for table `tn_sessions`
--
ALTER TABLE `tn_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_session_user` (`user_id`);

--
-- Indexes for table `tn_settings`
--
ALTER TABLE `tn_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_setting_key` (`key`);

--
-- Indexes for table `tn_sitemap_log`
--
ALTER TABLE `tn_sitemap_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tn_special_categories`
--
ALTER TABLE `tn_special_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_special_slug` (`slug`),
  ADD KEY `fk_sc_category` (`category_id`);

--
-- Indexes for table `tn_special_category_articles`
--
ALTER TABLE `tn_special_category_articles`
  ADD PRIMARY KEY (`special_id`,`article_id`),
  ADD KEY `fk_sca_article` (`article_id`);

--
-- Indexes for table `tn_states`
--
ALTER TABLE `tn_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_state_slug` (`slug`);

--
-- Indexes for table `tn_tags`
--
ALTER TABLE `tn_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tag_slug` (`slug`);

--
-- Indexes for table `tn_users`
--
ALTER TABLE `tn_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_email` (`email`),
  ADD KEY `fk_user_role` (`role_id`),
  ADD KEY `fk_user_district` (`assigned_district_id`);

--
-- Indexes for table `tn_user_badges`
--
ALTER TABLE `tn_user_badges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tn_user_badge_assignments`
--
ALTER TABLE `tn_user_badge_assignments`
  ADD PRIMARY KEY (`user_id`,`badge_id`),
  ADD KEY `fk_uba_badge` (`badge_id`);

--
-- Indexes for table `tn_youtube_channels`
--
ALTER TABLE `tn_youtube_channels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_yt_channel_id` (`channel_id`);

--
-- Indexes for table `tn_youtube_imports`
--
ALTER TABLE `tn_youtube_imports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_yt_import_video` (`video_id`),
  ADD KEY `fk_yi_channel` (`channel_id`),
  ADD KEY `fk_yi_article` (`article_id`);

--
-- Indexes for table `tn_youtube_keyword_map`
--
ALTER TABLE `tn_youtube_keyword_map`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kw_channel` (`channel_id`),
  ADD KEY `fk_kw_category` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tn_activity_log`
--
ALTER TABLE `tn_activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_ad_slots`
--
ALTER TABLE `tn_ad_slots`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tn_analytics_daily`
--
ALTER TABLE `tn_analytics_daily`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `tn_articles`
--
ALTER TABLE `tn_articles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `tn_article_ratings`
--
ALTER TABLE `tn_article_ratings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_categories`
--
ALTER TABLE `tn_categories`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tn_cities`
--
ALTER TABLE `tn_cities`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tn_contributors`
--
ALTER TABLE `tn_contributors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tn_cron_logs`
--
ALTER TABLE `tn_cron_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_csrf_tokens`
--
ALTER TABLE `tn_csrf_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_districts`
--
ALTER TABLE `tn_districts`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tn_editor_permissions`
--
ALTER TABLE `tn_editor_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tn_fcm_topics`
--
ALTER TABLE `tn_fcm_topics`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tn_linking_config`
--
ALTER TABLE `tn_linking_config`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tn_live_blogs`
--
ALTER TABLE `tn_live_blogs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_live_entries`
--
ALTER TABLE `tn_live_entries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_media`
--
ALTER TABLE `tn_media`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_newspapers`
--
ALTER TABLE `tn_newspapers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_notifications`
--
ALTER TABLE `tn_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tn_premium_access`
--
ALTER TABLE `tn_premium_access`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_premium_plans`
--
ALTER TABLE `tn_premium_plans`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tn_print_editions`
--
ALTER TABLE `tn_print_editions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tn_push_notifications`
--
ALTER TABLE `tn_push_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_readers`
--
ALTER TABLE `tn_readers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_reporter_assignments`
--
ALTER TABLE `tn_reporter_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_roles`
--
ALTER TABLE `tn_roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tn_rss_feeds`
--
ALTER TABLE `tn_rss_feeds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_rss_imports`
--
ALTER TABLE `tn_rss_imports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_seo_config`
--
ALTER TABLE `tn_seo_config`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tn_settings`
--
ALTER TABLE `tn_settings`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `tn_sitemap_log`
--
ALTER TABLE `tn_sitemap_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_special_categories`
--
ALTER TABLE `tn_special_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tn_states`
--
ALTER TABLE `tn_states`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tn_tags`
--
ALTER TABLE `tn_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `tn_users`
--
ALTER TABLE `tn_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tn_user_badges`
--
ALTER TABLE `tn_user_badges`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tn_youtube_channels`
--
ALTER TABLE `tn_youtube_channels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_youtube_imports`
--
ALTER TABLE `tn_youtube_imports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tn_youtube_keyword_map`
--
ALTER TABLE `tn_youtube_keyword_map`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tn_activity_log`
--
ALTER TABLE `tn_activity_log`
  ADD CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tn_analytics_daily`
--
ALTER TABLE `tn_analytics_daily`
  ADD CONSTRAINT `fk_analytics_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_articles`
--
ALTER TABLE `tn_articles`
  ADD CONSTRAINT `fk_article_category` FOREIGN KEY (`category_id`) REFERENCES `tn_categories` (`id`),
  ADD CONSTRAINT `fk_article_city` FOREIGN KEY (`city_id`) REFERENCES `tn_cities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_article_contributor` FOREIGN KEY (`contributor_id`) REFERENCES `tn_contributors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_article_media` FOREIGN KEY (`media_id`) REFERENCES `tn_media` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_article_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`);

--
-- Constraints for table `tn_article_ratings`
--
ALTER TABLE `tn_article_ratings`
  ADD CONSTRAINT `fk_rating_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rating_reader` FOREIGN KEY (`reader_id`) REFERENCES `tn_readers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_article_tags`
--
ALTER TABLE `tn_article_tags`
  ADD CONSTRAINT `fk_at_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_at_tag` FOREIGN KEY (`tag_id`) REFERENCES `tn_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_categories`
--
ALTER TABLE `tn_categories`
  ADD CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `tn_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tn_cities`
--
ALTER TABLE `tn_cities`
  ADD CONSTRAINT `fk_city_dist` FOREIGN KEY (`district_id`) REFERENCES `tn_districts` (`id`);

--
-- Constraints for table `tn_contributor_categories`
--
ALTER TABLE `tn_contributor_categories`
  ADD CONSTRAINT `fk_cc_category` FOREIGN KEY (`category_id`) REFERENCES `tn_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cc_contributor` FOREIGN KEY (`contributor_id`) REFERENCES `tn_contributors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_csrf_tokens`
--
ALTER TABLE `tn_csrf_tokens`
  ADD CONSTRAINT `fk_csrf_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_districts`
--
ALTER TABLE `tn_districts`
  ADD CONSTRAINT `fk_dist_state` FOREIGN KEY (`state_id`) REFERENCES `tn_states` (`id`);

--
-- Constraints for table `tn_editor_permissions`
--
ALTER TABLE `tn_editor_permissions`
  ADD CONSTRAINT `fk_ep_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_live_blogs`
--
ALTER TABLE `tn_live_blogs`
  ADD CONSTRAINT `fk_lb_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_lb_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`);

--
-- Constraints for table `tn_live_entries`
--
ALTER TABLE `tn_live_entries`
  ADD CONSTRAINT `fk_le_blog` FOREIGN KEY (`live_blog_id`) REFERENCES `tn_live_blogs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_le_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tn_media`
--
ALTER TABLE `tn_media`
  ADD CONSTRAINT `fk_media_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tn_newspapers`
--
ALTER TABLE `tn_newspapers`
  ADD CONSTRAINT `fk_np_user` FOREIGN KEY (`uploaded_by`) REFERENCES `tn_users` (`id`);

--
-- Constraints for table `tn_notifications`
--
ALTER TABLE `tn_notifications`
  ADD CONSTRAINT `fk_notif_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_premium_access`
--
ALTER TABLE `tn_premium_access`
  ADD CONSTRAINT `fk_pa_plan` FOREIGN KEY (`plan_id`) REFERENCES `tn_premium_plans` (`id`),
  ADD CONSTRAINT `fk_pa_reader` FOREIGN KEY (`reader_id`) REFERENCES `tn_readers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_print_editions`
--
ALTER TABLE `tn_print_editions`
  ADD CONSTRAINT `fk_pe_user` FOREIGN KEY (`created_by`) REFERENCES `tn_users` (`id`);

--
-- Constraints for table `tn_print_edition_articles`
--
ALTER TABLE `tn_print_edition_articles`
  ADD CONSTRAINT `fk_pea_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pea_edition` FOREIGN KEY (`edition_id`) REFERENCES `tn_print_editions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_push_notifications`
--
ALTER TABLE `tn_push_notifications`
  ADD CONSTRAINT `fk_pn_topic` FOREIGN KEY (`topic_id`) REFERENCES `tn_fcm_topics` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pn_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tn_reporter_assignments`
--
ALTER TABLE `tn_reporter_assignments`
  ADD CONSTRAINT `fk_ra_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ra_editor` FOREIGN KEY (`district_editor_id`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ra_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_rss_imports`
--
ALTER TABLE `tn_rss_imports`
  ADD CONSTRAINT `fk_ri_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ri_feed` FOREIGN KEY (`feed_id`) REFERENCES `tn_rss_feeds` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_sessions`
--
ALTER TABLE `tn_sessions`
  ADD CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_special_categories`
--
ALTER TABLE `tn_special_categories`
  ADD CONSTRAINT `fk_sc_category` FOREIGN KEY (`category_id`) REFERENCES `tn_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tn_special_category_articles`
--
ALTER TABLE `tn_special_category_articles`
  ADD CONSTRAINT `fk_sca_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sca_special` FOREIGN KEY (`special_id`) REFERENCES `tn_special_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_users`
--
ALTER TABLE `tn_users`
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `tn_roles` (`id`);

--
-- Constraints for table `tn_user_badge_assignments`
--
ALTER TABLE `tn_user_badge_assignments`
  ADD CONSTRAINT `fk_uba_badge` FOREIGN KEY (`badge_id`) REFERENCES `tn_user_badges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_uba_user` FOREIGN KEY (`user_id`) REFERENCES `tn_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_youtube_imports`
--
ALTER TABLE `tn_youtube_imports`
  ADD CONSTRAINT `fk_yi_article` FOREIGN KEY (`article_id`) REFERENCES `tn_articles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_yi_channel` FOREIGN KEY (`channel_id`) REFERENCES `tn_youtube_channels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tn_youtube_keyword_map`
--
ALTER TABLE `tn_youtube_keyword_map`
  ADD CONSTRAINT `fk_kw_category` FOREIGN KEY (`category_id`) REFERENCES `tn_categories` (`id`),
  ADD CONSTRAINT `fk_kw_channel` FOREIGN KEY (`channel_id`) REFERENCES `tn_youtube_channels` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
