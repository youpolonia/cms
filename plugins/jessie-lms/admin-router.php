<?php
/**
 * Jessie LMS — Admin Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = CMS_ROOT . '/plugins/jessie-lms';
require_once $pluginDir . '/includes/class-lms-course.php';
require_once $pluginDir . '/includes/class-lms-lesson.php';
require_once $pluginDir . '/includes/class-lms-enrollment.php';

\Core\Session::requireRole('admin');

$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/admin/lms' || $uri === '/admin/lms/') { require $pluginDir . '/views/admin/dashboard.php'; exit; }

// ─── COURSES ───
if ($uri === '/admin/lms/courses') { require $pluginDir . '/views/admin/courses.php'; exit; }
if ($uri === '/admin/lms/courses/create') { $course = null; require $pluginDir . '/views/admin/course-form.php'; exit; }

if (preg_match('#^/admin/lms/courses/(\d+)/edit$#', $uri, $m)) {
    $course = \LmsCourse::get((int)$m[1]);
    if (!$course) { \Core\Session::flash('error', 'Course not found.'); \Core\Response::redirect('/admin/lms/courses'); }
    require $pluginDir . '/views/admin/course-form.php'; exit;
}

if ($uri === '/admin/lms/courses/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    $id = \LmsCourse::create($data);
    \Core\Session::flash('success', 'Course created.'); \Core\Response::redirect('/admin/lms/courses/' . $id . '/edit');
}

if (preg_match('#^/admin/lms/courses/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \LmsCourse::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Course updated.'); \Core\Response::redirect('/admin/lms/courses/' . $m[1] . '/edit');
}

if (preg_match('#^/admin/lms/courses/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \LmsCourse::delete((int)$m[1]);
    \Core\Session::flash('success', 'Course deleted.'); \Core\Response::redirect('/admin/lms/courses');
}

// ─── LESSONS ───
if (preg_match('#^/admin/lms/courses/(\d+)/lessons/create$#', $uri, $m)) {
    $lesson = null; $_GET['course_id'] = $m[1];
    require $pluginDir . '/views/admin/lesson-form.php'; exit;
}

if (preg_match('#^/admin/lms/lessons/(\d+)/edit$#', $uri, $m)) {
    $lesson = \LmsLesson::get((int)$m[1]);
    if (!$lesson) { \Core\Session::flash('error', 'Lesson not found.'); \Core\Response::redirect('/admin/lms/courses'); }
    require $pluginDir . '/views/admin/lesson-form.php'; exit;
}

if ($uri === '/admin/lms/lessons/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \LmsLesson::create($data);
    \Core\Session::flash('success', 'Lesson created.'); \Core\Response::redirect('/admin/lms/courses/' . (int)$data['course_id'] . '/edit');
}

if (preg_match('#^/admin/lms/lessons/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    $lesson = \LmsLesson::get((int)$m[1]);
    \LmsLesson::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Lesson updated.'); \Core\Response::redirect('/admin/lms/lessons/' . $m[1] . '/edit');
}

if (preg_match('#^/admin/lms/lessons/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $lesson = \LmsLesson::get((int)$m[1]);
    \LmsLesson::delete((int)$m[1]);
    \Core\Session::flash('success', 'Lesson deleted.'); \Core\Response::redirect('/admin/lms/courses/' . ($lesson['course_id'] ?? '') . '/edit');
}

// ─── ENROLLMENTS ───
if ($uri === '/admin/lms/enrollments') {
    require $pluginDir . '/views/admin/enrollments.php';
    exit;
}

// ─── REVIEWS ───
if ($uri === '/admin/lms/reviews') {
    require $pluginDir . '/views/admin/reviews.php';
    exit;
}
if (preg_match('#^/admin/lms/reviews/(\d+)/(approve|reject)$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    require_once $pluginDir . '/includes/class-lms-review.php';
    if ($m[2] === 'approve') { \LmsReview::approve((int)$m[1]); }
    else { \LmsReview::reject((int)$m[1]); }
    \Core\Response::redirect('/admin/lms/reviews?tab=pending');
}

// ─── CERTIFICATES ───
if ($uri === '/admin/lms/certificates') {
    require $pluginDir . '/views/admin/certificates.php';
    exit;
}

// ─── QUIZ ───
if (preg_match('#^/admin/lms/lessons/(\d+)/quiz$#', $uri, $m)) {
    $lessonId = (int)$m[1];
    require $pluginDir . '/views/admin/quiz-form.php';
    exit;
}
if ($uri === '/admin/lms/quiz/save' && $method === 'POST') {
    csrf_validate_or_403();
    $pdo = db();
    $lessonId = (int)$_POST['lesson_id'];
    $questions = [];
    foreach ($_POST['q'] ?? [] as $q) {
        if (empty($q['text'])) continue;
        $questions[] = [
            'text' => trim($q['text']),
            'type' => $q['type'] ?? 'multiple',
            'options' => $q['options'] ?? [],
            'correct' => $q['correct'] ?? '',
        ];
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS `lms_quizzes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY, `lesson_id` INT NOT NULL, `questions` JSON NOT NULL,
        `passing_score` INT DEFAULT 70, `max_attempts` INT DEFAULT 3, `time_limit_minutes` INT DEFAULT 0,
        `shuffle_questions` TINYINT DEFAULT 0, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY (`lesson_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->prepare("INSERT INTO lms_quizzes (lesson_id, questions, passing_score, max_attempts, time_limit_minutes, shuffle_questions)
        VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE questions=VALUES(questions), passing_score=VALUES(passing_score),
        max_attempts=VALUES(max_attempts), time_limit_minutes=VALUES(time_limit_minutes), shuffle_questions=VALUES(shuffle_questions)")
        ->execute([$lessonId, json_encode($questions), (int)($_POST['passing_score'] ?? 70), (int)($_POST['max_attempts'] ?? 3),
            (int)($_POST['time_limit_minutes'] ?? 0), isset($_POST['shuffle_questions']) ? 1 : 0]);
    \Core\Response::redirect('/admin/lms/lessons/' . $lessonId . '/quiz?saved=1');
}

// ─── SETTINGS ───
if ($uri === '/admin/lms/settings') {
    require $pluginDir . '/views/admin/settings.php';
    exit;
}

if ($uri === '/admin/lms/settings/save' && $method === 'POST') {
    csrf_validate_or_403();
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS `lms_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $fields = ['enrollment_mode','certificate_enabled','certificate_template','passing_grade','max_attempts','show_answers','progress_tracking','course_reviews','items_per_page'];
    $checkboxes = ['certificate_enabled','show_answers','progress_tracking','course_reviews'];
    foreach ($fields as $f) {
        $val = in_array($f, $checkboxes) ? (isset($_POST[$f]) ? '1' : '0') : ($_POST[$f] ?? '');
        $pdo->prepare("INSERT INTO `lms_settings` (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?")->execute([$f, $val, $val]);
    }
    \Core\Session::flash('success', 'Settings saved!');
    \Core\Response::redirect('/admin/lms/settings?saved=1');
}

