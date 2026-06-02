-- ============================================================
-- SIMPLIFY AD SLOTS TO 2 TYPES ONLY
-- Square (300x250) and Horizontal (728x90 / 320x50 mobile)
-- ============================================================
USE `tamilnews_db`;

-- Clear existing slots and replace with 2 only
TRUNCATE TABLE `tn_ad_slots`;

INSERT INTO `tn_ad_slots` (`id`,`name`,`slug`,`position`,`desktop_size`,`mobile_size`,`is_active`) VALUES
(1, 'Square Ad',     'square',     'square',     '300x250', '300x250', 1),
(2, 'Horizontal Ad', 'horizontal', 'horizontal', '728x90',  '320x50',  1);

-- Update any existing business ads to use slot 1 or 2
UPDATE `tn_business_ads` SET `slot_id` = 1 WHERE `slot_id` NOT IN (1,2);
