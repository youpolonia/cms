<?php
/**
 * Jessie Portfolio — API Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-portfolio-project.php';
require_once $pluginDir . '/includes/class-portfolio-category.php';
require_once $pluginDir . '/includes/class-portfolio-testimonial.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/portfolio/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        case 'projects':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $filters = ['status' => 'published'];
            if (!empty($_GET['category_id'])) $filters['category_id'] = $_GET['category_id'];
            if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
            if (!empty($_GET['featured'])) $filters['featured'] = true;
            echo json_encode(['ok' => true] + \PortfolioProject::getAll($filters, $page));
            exit;

        case 'project':
            if ($id) {
                $project = \PortfolioProject::get($id);
                if ($project && $project['status'] === 'published') \PortfolioProject::incrementViews($id);
                $testimonials = $project ? \PortfolioTestimonial::getForProject($id) : [];
                echo json_encode(['ok' => (bool)$project, 'project' => $project, 'testimonials' => $testimonials]);
                exit;
            }
            break;

        case 'categories':
            echo json_encode(['ok' => true, 'categories' => \PortfolioCategory::getWithCounts()]);
            exit;

        case 'testimonials':
            $testimonials = \PortfolioTestimonial::getAll('published');
            echo json_encode(['ok' => true, 'testimonials' => $testimonials]);
            exit;

        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true, 'stats' => \PortfolioProject::getStats()]);
            exit;

        case 'ai-case-study':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            if ($method !== 'POST') break;
            if (!function_exists('ai_universal_generate')) {
                require_once CMS_ROOT . '/core/ai_content.php';
            }
            $projectId = (int)($input['project_id'] ?? $id ?? 0);
            $project = $projectId ? \PortfolioProject::get($projectId) : null;
            $title = $input['title'] ?? ($project['title'] ?? '');
            $desc = $input['description'] ?? ($project['description'] ?? '');
            $techs = $input['technologies'] ?? ($project ? implode(', ', $project['technologies'] ?? []) : '');
            $client = $input['client_name'] ?? ($project['client_name'] ?? '');

            if (!$title) {
                echo json_encode(['ok' => false, 'error' => 'Project title is required']);
                exit;
            }

            $ai = ai_config_load();
            $provider = $ai['provider'] ?? 'openai';
            $model = $ai['model'] ?? 'gpt-4o-mini';

            $systemPrompt = 'You are a professional portfolio copywriter. Write compelling case studies for web/software projects.';
            $userPrompt = "Write a professional case study for a portfolio project.\n\n"
                . "Project: {$title}\n"
                . ($client ? "Client: {$client}\n" : '')
                . ($techs ? "Technologies: {$techs}\n" : '')
                . ($desc ? "Current description: " . substr($desc, 0, 500) . "\n" : '')
                . "\nReturn JSON: {\"case_study\": \"3-4 paragraphs covering challenge, solution, results\", \"short_description\": \"1-2 sentences\", \"highlights\": [\"highlight 1\", \"highlight 2\", \"highlight 3\"]}\nReturn ONLY valid JSON.";

            $result = ai_universal_generate($provider, $model, $systemPrompt, $userPrompt, ['max_tokens' => 1000, 'temperature' => 0.7]);
            if (!empty($result['ok']) && !empty($result['content'])) {
                $content = $result['content'];
                if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $cm)) $content = $cm[1];
                $data = json_decode($content, true);
                if (!$data && preg_match('/\{[\s\S]*\}/', $content, $cm)) $data = json_decode($cm[0], true);
                echo json_encode($data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'Failed to parse AI response']);
            } else {
                echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'AI generation failed']);
            }
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
