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
    require_once $pluginDir . '/includes/class-lms-enrollment.php';
    echo '<pre>'; print_r(\LmsEnrollment::getStats()); // TODO: proper view
    exit;
}
