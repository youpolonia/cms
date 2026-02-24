<?php
/**
 * Jessie LMS — API Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-lms-course.php';
require_once $pluginDir . '/includes/class-lms-lesson.php';
require_once $pluginDir . '/includes/class-lms-enrollment.php';
require_once $pluginDir . '/includes/class-lms-ai.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/lms/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        case 'courses':
            echo json_encode(['ok' => true] + \LmsCourse::getAll(['status' => 'published']));
            exit;

        case 'course':
            if ($id) { echo json_encode(['ok' => true, 'course' => \LmsCourse::get($id), 'lessons' => \LmsLesson::getGrouped($id)]); exit; }
            break;

        case 'enroll':
            if ($method !== 'POST') break;
            $courseId = (int)($input['course_id'] ?? $id ?? 0);
            $email = $input['email'] ?? '';
            if (!$courseId || !$email) { echo json_encode(['ok' => false, 'error' => 'course_id and email required']); exit; }
            echo json_encode(\LmsEnrollment::enroll($courseId, $email, $input['name'] ?? '', isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null));
            exit;

        case 'complete-lesson':
            if ($method !== 'POST') break;
            echo json_encode(\LmsEnrollment::completeLesson((int)($input['enrollment_id'] ?? 0), (int)($input['lesson_id'] ?? 0)));
            exit;

        case 'enrollments':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(['ok' => true, 'stats' => \LmsEnrollment::getStats()]);
            exit;

        // AI
        case 'ai-outline':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\LmsAI::generateOutline($input['topic'] ?? '', $input['difficulty'] ?? 'beginner', (int)($input['lessons'] ?? 8), $input['language'] ?? 'en'));
            exit;

        case 'ai-lesson':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\LmsAI::generateLessonContent($input['title'] ?? '', $input['course_title'] ?? '', $input['difficulty'] ?? 'beginner', $input['language'] ?? 'en'));
            exit;

        case 'ai-quiz':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\LmsAI::generateQuiz($input['content'] ?? '', (int)($input['num_questions'] ?? 5), $input['language'] ?? 'en'));
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
