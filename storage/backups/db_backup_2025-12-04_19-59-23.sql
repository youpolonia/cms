-- CMS Database Backup
-- Generated: 2025-12-04 19:59:23
-- Tables: 22

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int unsigned DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_action` (`action`),
  KEY `idx_user` (`user_id`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_logs` (`id`, `user_id`, `username`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES ('1', NULL, 'system', 'test_action', 'test', NULL, 'Module initialization test', '127.0.0.1', NULL, '2025-12-04 18:14:39');

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`, `last_login`, `updated_at`) VALUES ('1', 'admin', NULL, '$2y$10$pkdfSl1IwJS/5M0tcm5DZuEKRP3PBp/FQ/b2rhGhVz6UomudrBVCm', 'admin', '2025-11-08 11:21:19', NULL, '2025-12-04 15:11:12');

DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `featured_image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `category_id` int unsigned DEFAULT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category_id`),
  KEY `idx_published` (`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `articles` (`id`, `slug`, `title`, `excerpt`, `content`, `featured_image`, `meta_title`, `meta_description`, `status`, `category_id`, `author_id`, `published_at`, `created_at`, `updated_at`) VALUES ('1', 'test-article', 'Updated Test Article', 'Updated excerpt', 'Updated content', NULL, '', '', 'published', '1', '1', '2025-12-04 13:24:23', '2025-12-04 13:23:44', '2025-12-04 13:24:23');
INSERT INTO `articles` (`id`, `slug`, `title`, `excerpt`, `content`, `featured_image`, `meta_title`, `meta_description`, `status`, `category_id`, `author_id`, `published_at`, `created_at`, `updated_at`) VALUES ('2', 'teest', 'Teest', 'Test', '', NULL, '', '', 'draft', NULL, '1', NULL, '2025-12-04 13:41:00', '2025-12-04 13:41:00');

DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('database','files','full') COLLATE utf8mb4_unicode_ci DEFAULT 'database',
  `size_bytes` bigint unsigned DEFAULT '0',
  `tables_count` int unsigned DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` int unsigned DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `slug`, `name`, `description`, `parent_id`, `sort_order`, `created_at`, `updated_at`) VALUES ('1', 'uncategorized', 'Uncategorized', 'Default category', NULL, '0', '2025-12-04 13:18:21', '2025-12-04 18:01:49');
INSERT INTO `categories` (`id`, `slug`, `name`, `description`, `parent_id`, `sort_order`, `created_at`, `updated_at`) VALUES ('2', 'test', 'test', 'test', NULL, '0', '2025-12-04 18:08:59', '2025-12-04 18:08:59');

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int unsigned DEFAULT NULL,
  `page_id` int unsigned DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `author_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','spam','trash') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_article` (`article_id`),
  KEY `idx_page` (`page_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` (`id`, `article_id`, `page_id`, `parent_id`, `author_name`, `author_email`, `content`, `status`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES ('1', NULL, NULL, NULL, 'Test User', 'test@example.com', 'This is a test comment to verify the module works correctly.', 'pending', '127.0.0.1', NULL, '2025-12-04 15:25:39', '2025-12-04 15:25:39');

DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `content_blocks`;
CREATE TABLE `content_blocks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `type` enum('html','text','json') COLLATE utf8mb4_unicode_ci DEFAULT 'html',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `content_blocks` (`id`, `name`, `slug`, `content`, `type`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'test', 'test', 'test', 'html', '', '1', '2025-12-04 19:37:53', '2025-12-04 19:37:53');

DROP TABLE IF EXISTS `galleries`;
CREATE TABLE `galleries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `galleries` (`id`, `name`, `slug`, `description`, `cover_image`, `is_public`, `sort_order`, `created_at`, `updated_at`) VALUES ('1', 'Test Gallery', 'test-gallery', 'A test gallery', NULL, '1', '0', '2025-12-04 17:37:42', '2025-12-04 17:37:42');

DROP TABLE IF EXISTS `gallery_images`;
CREATE TABLE `gallery_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `gallery_id` int unsigned NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gallery` (`gallery_id`),
  CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` int unsigned NOT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_id` int unsigned DEFAULT NULL,
  `target` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '_self',
  `css_class` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `idx_menu` (`menu_id`),
  KEY `idx_sort` (`sort_order`),
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menus` (`id`, `name`, `slug`, `description`, `location`, `created_at`) VALUES ('1', 'test', 'test', '', 'header', '2025-12-04 16:18:01');

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int DEFAULT NULL,
  PRIMARY KEY (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2025_05_17_031700_create_user_tables', '1');

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES ('1', 'about', 'About', '<p>About page — initial content.</p>', 'published', '2025-11-07 16:15:46', '2025-11-07 16:15:46');
INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES ('2', 'contact', 'Contact', '<p>Contact page — initial content.</p>', 'published', '2025-11-07 16:15:46', '2025-11-07 16:15:46');
INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES ('3', 'test-page-audit', 'Test Page', 'Test content', 'draft', '2025-12-04 12:23:34', '2025-12-04 12:23:34');
INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES ('4', 'test-via-curl', 'Test Via Curl', 'Test content', 'published', '2025-12-04 12:25:18', '2025-12-04 12:26:43');
INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES ('5', 'trailing-test-123', 'TrailingSlashTest', 'Test', 'draft', '2025-12-04 12:32:10', '2025-12-04 12:32:10');
INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES ('6', 'fixed-test', 'Fixed Test Page', 'This should work now', 'published', '2025-12-04 12:44:01', '2025-12-04 12:44:01');
INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `status`, `created_at`, `updated_at`) VALUES ('7', 'test-via-browser', 'Test Via Browser', 'test', 'draft', '2025-12-04 12:45:46', '2025-12-04 12:45:46');

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `redirects`;
CREATE TABLE `redirects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_code` smallint DEFAULT '301',
  `hits` int unsigned DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_source` (`source_url`(191)),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES ('1', 'admin', 'System Administrator', '2025-07-14 22:18:43');

DROP TABLE IF EXISTS `search_logs`;
CREATE TABLE `search_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `query` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `results_count` int unsigned DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_query` (`query`(191)),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text,
  `group_name` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `sites`;
CREATE TABLE `sites` (
  `id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `widgets`;
CREATE TABLE `widgets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('html','text','menu','recent_posts','categories','custom') COLLATE utf8mb4_unicode_ci DEFAULT 'html',
  `area` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sidebar',
  `content` text COLLATE utf8mb4_unicode_ci,
  `settings` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_area` (`area`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `widgets` (`id`, `name`, `slug`, `type`, `area`, `content`, `settings`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES ('1', 'Sidebar Welcome', 'sidebar-welcome', 'html', 'sidebar', '<h3>Welcome!</h3><p>Thanks for visiting our site.</p>', NULL, '1', '1', '2025-12-04 16:24:45', '2025-12-04 16:24:45');
INSERT INTO `widgets` (`id`, `name`, `slug`, `type`, `area`, `content`, `settings`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES ('2', 'Test Widget 2', 'test-widget-2', 'html', 'sidebar', '<p>Test content</p>', NULL, '1', '2', '2025-12-04 17:17:45', '2025-12-04 17:17:45');
INSERT INTO `widgets` (`id`, `name`, `slug`, `type`, `area`, `content`, `settings`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES ('3', 'test', 'test', 'html', 'sidebar', 'test', NULL, '1', '3', '2025-12-04 17:25:22', '2025-12-04 17:25:22');

SET FOREIGN_KEY_CHECKS=1;
