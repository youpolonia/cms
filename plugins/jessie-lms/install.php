<?php
/**
 * Jessie LMS — Database Installation
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';

$pdo = db();

$pdo->exec("CREATE TABLE IF NOT EXISTS lms_courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500) DEFAULT '',
    instructor_name VARCHAR(100) DEFAULT '',
    instructor_bio TEXT,
    thumbnail VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) DEFAULT '',
    difficulty ENUM('beginner','intermediate','advanced','all') DEFAULT 'all',
    price DECIMAL(10,2) DEFAULT 0,
    is_free TINYINT(1) DEFAULT 0,
    duration_hours DECIMAL(5,1) DEFAULT 0,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    featured TINYINT(1) DEFAULT 0,
    enrollment_count INT UNSIGNED DEFAULT 0,
    completion_count INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS lms_lessons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) DEFAULT '',
    content_type ENUM('text','video','quiz','download','assignment') DEFAULT 'text',
    content_html LONGTEXT,
    video_url VARCHAR(500) DEFAULT NULL,
    duration_minutes INT UNSIGNED DEFAULT 0,
    sort_order INT UNSIGNED DEFAULT 0,
    section VARCHAR(100) DEFAULT '',
    is_preview TINYINT(1) DEFAULT 0,
    status ENUM('draft','published') DEFAULT 'published',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_course (course_id),
    KEY idx_order (course_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS lms_enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(100) DEFAULT '',
    status ENUM('active','completed','dropped') DEFAULT 'active',
    progress_pct DECIMAL(5,2) DEFAULT 0,
    completed_lessons JSON DEFAULT NULL,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    certificate_id VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_course_user (course_id, email),
    KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS lms_quizzes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT UNSIGNED NOT NULL,
    questions JSON NOT NULL,
    passing_score INT UNSIGNED DEFAULT 70,
    time_limit_minutes INT UNSIGNED DEFAULT 0,
    max_attempts INT UNSIGNED DEFAULT 3,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_lesson (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS lms_quiz_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT UNSIGNED NOT NULL,
    enrollment_id INT UNSIGNED NOT NULL,
    answers JSON DEFAULT NULL,
    score INT UNSIGNED DEFAULT 0,
    passed TINYINT(1) DEFAULT 0,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    KEY idx_quiz (quiz_id),
    KEY idx_enrollment (enrollment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "LMS tables created successfully.\n";
