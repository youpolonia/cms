<?php
/**
 * Jessie Jobs — API Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-job-listing.php';
require_once $pluginDir . '/includes/class-job-application.php';
require_once $pluginDir . '/includes/class-job-company.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/jobs/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        case 'jobs':
            echo json_encode(['ok' => true] + \JobListing::getAll(array_merge($_GET, ['status' => 'active'])));
            exit;

        case 'job':
            if ($id) {
                $job = \JobListing::get($id);
                if ($job && $job['status'] === 'active') \JobListing::incrementViews($id);
                echo json_encode(['ok' => (bool)$job, 'job' => $job]);
                exit;
            }
            break;

        case 'search':
            $q = $_GET['q'] ?? '';
            $filters = array_merge($_GET, ['search' => $q, 'status' => 'active']);
            echo json_encode(['ok' => true] + \JobListing::getAll($filters));
            exit;

        case 'companies':
            echo json_encode(['ok' => true, 'companies' => \JobCompany::getAll('active')]);
            exit;

        case 'apply':
            if ($method !== 'POST') break;
            if (empty($input['job_id']) || empty($input['applicant_name']) || empty($input['applicant_email'])) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Name, email, and job are required.']);
                exit;
            }
            $job = \JobListing::get((int)$input['job_id']);
            if (!$job || $job['status'] !== 'active') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Job not found or not active.']);
                exit;
            }
            $appId = \JobApplication::create($input);
            echo json_encode(['ok' => true, 'application_id' => $appId, 'message' => 'Application submitted successfully.']);
            exit;

        // ADMIN
        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true, 'stats' => \JobListing::getStats()]);
            exit;

        case 'application-status':
            if (!$isAdmin || !$id) break;
            $status = $input['status'] ?? '';
            \JobApplication::updateStatus($id, $status);
            echo json_encode(['ok' => true]); exit;

        // AI
        case 'ai-description':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }
            $prompt = "Write a professional job listing description.\n\n"
                . "Job Title: " . ($input['title'] ?? '') . "\n"
                . "Company: " . ($input['company'] ?? '') . "\n"
                . "Type: " . ($input['job_type'] ?? '') . "\n"
                . "Location: " . ($input['location'] ?? '') . "\n\n"
                . "Return JSON: {\"description\": \"3-4 paragraph professional job description\", \"benefits\": \"bullet points of benefits\"}\n"
                . "Return ONLY valid JSON.";
            $response = ai_universal_generate($prompt, ['max_tokens' => 600, 'temperature' => 0.4]);
            $data = self_parseJson($response);
            echo json_encode($data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed']);
            exit;

        case 'ai-requirements':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }
            $prompt = "Generate job requirements and skills.\n\n"
                . "Job Title: " . ($input['title'] ?? '') . "\n"
                . "Experience Level: " . ($input['experience_level'] ?? 'mid') . "\n"
                . "Category: " . ($input['category'] ?? '') . "\n\n"
                . "Return JSON: {\"requirements\": \"bullet points of requirements\", \"skills\": [\"skill1\", \"skill2\", \"skill3\", \"skill4\", \"skill5\"]}\n"
                . "Return ONLY valid JSON.";
            $response = ai_universal_generate($prompt, ['max_tokens' => 500, 'temperature' => 0.4]);
            $data = self_parseJson($response);
            echo json_encode($data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed']);
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);

function self_parseJson(string $response): ?array
{
    $response = trim($response);
    if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $m)) $response = $m[1];
    $data = json_decode($response, true);
    if ($data) return $data;
    if (preg_match('/\{[\s\S]*\}/', $response, $m)) return json_decode($m[0], true);
    return null;
}
