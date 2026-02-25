<?php
/**
 * Jessie LMS — API Router (upgraded)
 * ~30 endpoints: public catalog, enrollment, progress, reviews, certificates, quizzes, admin CRUD, AI
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-lms-course.php';
require_once $pluginDir . '/includes/class-lms-lesson.php';
require_once $pluginDir . '/includes/class-lms-enrollment.php';
require_once $pluginDir . '/includes/class-lms-review.php';
require_once $pluginDir . '/includes/class-lms-certificate.php';
require_once $pluginDir . '/includes/class-lms-ai.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/lms/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        // ─── PUBLIC: Course Catalog ───
        case 'courses':
            $filters = ['status' => 'published', 'category' => $_GET['category'] ?? '', 'search' => $_GET['q'] ?? ''];
            echo json_encode(['ok' => true] + \LmsCourse::getAll($filters, max(1, (int)($_GET['page'] ?? 1)), 12));
            exit;

        case 'categories':
            echo json_encode(['ok' => true, 'categories' => \LmsCourse::getCategories()]);
            exit;

        case 'course':
            if ($id) {
                $course = \LmsCourse::get($id);
                if (!$course || ($course['status'] !== 'published' && !$isAdmin)) { echo json_encode(['ok' => false, 'error' => 'Not found']); exit; }
                echo json_encode(['ok' => true, 'course' => $course, 'lessons' => \LmsLesson::getGrouped($id), 'reviews' => \LmsReview::getForCourse($id)]);
                exit;
            }
            break;

        case 'lessons':
            if ($id) {
                $lessons = \LmsLesson::getByCourse($id);
                $email = $_GET['email'] ?? '';
                $enrolled = false;
                if ($email) { $c = db()->prepare("SELECT id FROM lms_enrollments WHERE course_id = ? AND email = ? AND status = 'active'"); $c->execute([$id, $email]); $enrolled = (bool)$c->fetch(); }
                if (!$enrolled && !$isAdmin) {
                    $lessons = array_map(function($l) { if (!$l['is_preview']) { $l['content_html'] = null; $l['video_url'] = null; } return $l; }, $lessons);
                }
                echo json_encode(['ok' => true, 'lessons' => $lessons, 'enrolled' => $enrolled]);
                exit;
            }
            break;

        case 'lesson':
            if ($id) { $l = \LmsLesson::get($id); echo json_encode($l ? ['ok'=>true,'lesson'=>$l] : ['ok'=>false,'error'=>'Not found']); exit; }
            break;

        // ─── Enrollment ───
        case 'enroll':
            if ($method !== 'POST') break;
            $courseId = (int)($input['course_id'] ?? $id ?? 0);
            $email = trim($input['email'] ?? '');
            if (!$courseId || !$email) { echo json_encode(['ok' => false, 'error' => 'course_id and email required']); exit; }
            echo json_encode(\LmsEnrollment::enroll($courseId, $email, $input['name'] ?? '', isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null));
            exit;

        case 'complete-lesson':
            if ($method !== 'POST') break;
            echo json_encode(\LmsEnrollment::completeLesson((int)($input['enrollment_id'] ?? 0), (int)($input['lesson_id'] ?? 0)));
            exit;

        case 'my-courses':
            $email = $_GET['email'] ?? '';
            if (!$email) { echo json_encode(['ok' => false, 'error' => 'email required']); exit; }
            $stmt = db()->prepare("SELECT e.*, c.title, c.slug, c.thumbnail, c.category, c.difficulty, c.instructor_name, c.duration_hours FROM lms_enrollments e JOIN lms_courses c ON e.course_id = c.id WHERE e.email = ? ORDER BY e.last_activity DESC");
            $stmt->execute([strtolower(trim($email))]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as &$r) $r['completed_lessons'] = json_decode($r['completed_lessons'] ?: '[]', true);
            echo json_encode(['ok' => true, 'enrollments' => $rows]);
            exit;

        case 'progress':
            if ($id) {
                $e = \LmsEnrollment::get($id);
                if (!$e) { echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }
                $stmt = db()->prepare("SELECT COUNT(*) FROM lms_lessons WHERE course_id = ? AND status = 'published'");
                $stmt->execute([$e['course_id']]); $total = (int)$stmt->fetchColumn();
                echo json_encode(['ok'=>true,'progress'=>(float)$e['progress_pct'],'completed_count'=>count($e['completed_lessons']),'total'=>$total,'status'=>$e['status'],'certificate_id'=>$e['certificate_id']]);
                exit;
            }
            break;

        // ─── Reviews ───
        case 'reviews':
            if ($id) { echo json_encode(['ok'=>true,'reviews'=>\LmsReview::getForCourse($id)]); exit; }
            break;

        case 'review':
            if ($method !== 'POST') break;
            echo json_encode(\LmsReview::create((int)($input['course_id']??0), $input['email']??'', $input['name']??'', (int)($input['rating']??5), $input['review']??''));
            exit;

        // ─── Certificates ───
        case 'certificate':
            if ($method === 'POST') { echo json_encode(\LmsCertificate::generate((int)($input['enrollment_id']??0))); exit; }
            break;

        case 'verify-cert':
            $code = $_GET['code'] ?? $input['code'] ?? '';
            $cert = \LmsCertificate::verify($code);
            echo json_encode($cert ? ['ok'=>true,'valid'=>true,'certificate'=>$cert] : ['ok'=>true,'valid'=>false]);
            exit;

        case 'my-certificates':
            $email = $_GET['email'] ?? '';
            if (!$email) { echo json_encode(['ok'=>false,'error'=>'email required']); exit; }
            echo json_encode(['ok'=>true,'certificates'=>\LmsCertificate::getForStudent($email)]);
            exit;

        // ─── Quizzes ───
        case 'quiz':
            if ($id) {
                $q = db()->prepare("SELECT * FROM lms_quizzes WHERE lesson_id = ?"); $q->execute([$id]);
                $quiz = $q->fetch(\PDO::FETCH_ASSOC);
                if (!$quiz) { echo json_encode(['ok'=>false,'error'=>'No quiz']); exit; }
                $questions = json_decode($quiz['questions'] ?: '[]', true);
                foreach ($questions as &$qq) { unset($qq['correct'], $qq['explanation']); }
                $quiz['questions'] = $questions;
                echo json_encode(['ok'=>true,'quiz'=>$quiz]);
                exit;
            }
            break;

        case 'quiz-submit':
            if ($method !== 'POST') break;
            $quizId = (int)($input['quiz_id']??0);
            $enrollmentId = (int)($input['enrollment_id']??0);
            $answers = $input['answers'] ?? [];
            if (!$quizId || !$enrollmentId) { echo json_encode(['ok'=>false,'error'=>'quiz_id and enrollment_id required']); exit; }

            $pdo = db();
            $q = $pdo->prepare("SELECT * FROM lms_quizzes WHERE id = ?"); $q->execute([$quizId]);
            $quiz = $q->fetch(\PDO::FETCH_ASSOC);
            if (!$quiz) { echo json_encode(['ok'=>false,'error'=>'Quiz not found']); exit; }

            $attStmt = $pdo->prepare("SELECT COUNT(*) FROM lms_quiz_attempts WHERE quiz_id = ? AND enrollment_id = ?");
            $attStmt->execute([$quizId, $enrollmentId]);
            if ((int)$quiz['max_attempts'] > 0 && (int)$attStmt->fetchColumn() >= (int)$quiz['max_attempts']) {
                echo json_encode(['ok'=>false,'error'=>'Max attempts reached']); exit;
            }

            $questions = json_decode($quiz['questions'] ?: '[]', true);
            $correct = 0; $total = count($questions); $results = [];
            foreach ($questions as $i => $question) {
                $userAns = $answers[$i] ?? null;
                $isCorrect = (string)$userAns === (string)($question['correct'] ?? '');
                if ($isCorrect) $correct++;
                $results[] = ['question'=>$question['question']??'','your_answer'=>$userAns,'correct_answer'=>$question['correct']??'','is_correct'=>$isCorrect,'explanation'=>$question['explanation']??''];
            }
            $score = $total > 0 ? (int)round($correct / $total * 100) : 0;
            $passed = $score >= (int)$quiz['passing_score'];

            $pdo->prepare("INSERT INTO lms_quiz_attempts (quiz_id, enrollment_id, answers, score, passed, completed_at) VALUES (?,?,?,?,?,NOW())")->execute([$quizId, $enrollmentId, json_encode($answers), $score, $passed?1:0]);

            if ($passed) {
                $ls = $pdo->prepare("SELECT lesson_id FROM lms_quizzes WHERE id = ?"); $ls->execute([$quizId]);
                $lessonId = (int)$ls->fetchColumn();
                if ($lessonId) \LmsEnrollment::completeLesson($enrollmentId, $lessonId);
            }
            echo json_encode(['ok'=>true,'score'=>$score,'passed'=>$passed,'correct'=>$correct,'total'=>$total,'results'=>$results]);
            exit;

        // ─── ADMIN ───
        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            echo json_encode(['ok'=>true,'course_stats'=>\LmsCourse::getStats(),'enrollment_stats'=>\LmsEnrollment::getStats()]);
            exit;

        case 'enrollments':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            $cid = (int)($_GET['course_id'] ?? $id ?? 0);
            echo json_encode($cid ? ['ok'=>true]+\LmsEnrollment::getByCourse($cid) : ['ok'=>true,'stats'=>\LmsEnrollment::getStats()]);
            exit;

        case 'all-certificates':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            echo json_encode(['ok'=>true]+\LmsCertificate::getAll());
            exit;

        case 'pending-reviews':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            echo json_encode(['ok'=>true,'reviews'=>\LmsReview::getPending()]);
            exit;

        case 'approve-review':
            if ($method!=='POST'||!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            \LmsReview::approve((int)($input['id']??$id??0));
            echo json_encode(['ok'=>true]);
            exit;

        case 'reject-review':
            if ($method!=='POST'||!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            \LmsReview::reject((int)($input['id']??$id??0));
            echo json_encode(['ok'=>true]);
            exit;

        case 'admin-create-course':
            if ($method!=='POST'||!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            echo json_encode(['ok'=>true,'id'=>\LmsCourse::create($input)]);
            exit;

        case 'admin-update-course':
            if ($method!=='POST'||!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            \LmsCourse::update((int)($input['id']??$id??0), $input);
            echo json_encode(['ok'=>true]);
            exit;

        case 'admin-delete-course':
            if ($method!=='POST'||!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            \LmsCourse::delete((int)($input['id']??$id??0));
            echo json_encode(['ok'=>true]);
            exit;

        case 'admin-create-lesson':
            if ($method!=='POST'||!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            echo json_encode(['ok'=>true,'id'=>\LmsLesson::create($input)]);
            exit;

        case 'admin-update-lesson':
            if ($method!=='POST'||!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            \LmsLesson::update((int)($input['id']??$id??0), $input);
            echo json_encode(['ok'=>true]);
            exit;

        case 'admin-delete-lesson':
            if ($method!=='POST'||!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            \LmsLesson::delete((int)($input['id']??$id??0));
            echo json_encode(['ok'=>true]);
            exit;

        case 'admin-reorder':
            if ($method!=='POST'||!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            \LmsLesson::reorder((int)($input['course_id']??0), $input['lesson_ids']??[]);
            echo json_encode(['ok'=>true]);
            exit;

        // ─── AI ───
        case 'ai-outline':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            echo json_encode(\LmsAI::generateOutline($input['topic']??'', $input['difficulty']??'beginner', (int)($input['lessons']??8), $input['language']??'en'));
            exit;

        case 'ai-lesson':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            echo json_encode(\LmsAI::generateLessonContent($input['title']??'', $input['course_title']??'', $input['difficulty']??'beginner', $input['language']??'en'));
            exit;

        case 'ai-quiz':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
            echo json_encode(\LmsAI::generateQuiz($input['content']??'', (int)($input['num_questions']??5), $input['language']??'en'));
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
