<?php
/**
 * SEO Writer API Router — /api/seowriter/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-auth.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-credits.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-api-gateway.php';
require_once __DIR__ . '/../includes/class-seowriter-core.php';
require_once __DIR__ . '/../includes/class-seowriter-editor.php';
require_once __DIR__ . '/../includes/class-seowriter-keyword.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api/seowriter/?#', '', $uri);
$path = trim($path, '/');

header('Content-Type: application/json; charset=utf-8');

use Plugins\JessieSaasCore\{SaasApiGateway, SaasCredits};
use Plugins\JessieSeoWriter\{SeoWriterCore, SeoWriterEditor, SeoWriterKeyword};

try {
    // All endpoints require auth
    $gw = new SaasApiGateway();
    $authResult = $gw->authenticate();
    if (!$authResult['success']) {
        http_response_code($authResult['code'] ?? 401);
        echo json_encode($authResult);
        exit;
    }
    $userId = $gw->getUserId();
    $core = new SeoWriterCore($userId);

    // GET /api/seowriter/stats
    if ($method === 'GET' && $path === 'stats') {
        echo json_encode(['success' => true, 'stats' => $core->getDashboardStats()]);
        exit;
    }

    // GET /api/seowriter/projects
    if ($method === 'GET' && $path === 'projects') {
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 50)));
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        echo json_encode(['success' => true, 'projects' => $core->getProjects($limit, $offset)]);
        exit;
    }

    // POST /api/seowriter/projects
    if ($method === 'POST' && $path === 'projects') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = $core->createProject(
            $data['name'] ?? '',
            $data['description'] ?? '',
            $data['target_keyword'] ?? '',
            $data['language'] ?? 'en'
        );
        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    // PUT /api/seowriter/projects/{id}
    if ($method === 'POST' && preg_match('#^projects/(\d+)$#', $path, $m)) {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $core->updateProject((int)$m[1], $data);
        echo json_encode(['success' => true]);
        exit;
    }

    // GET /api/seowriter/content
    if ($method === 'GET' && $path === 'content') {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 50)));
        echo json_encode(['success' => true, 'content' => $core->getContentList($projectId, $limit)]);
        exit;
    }

    // GET /api/seowriter/content/{id}
    if ($method === 'GET' && preg_match('#^content/(\d+)$#', $path, $m)) {
        $item = $core->getContent((int)$m[1]);
        if (!$item) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Content not found']);
            exit;
        }
        echo json_encode(['success' => true, 'content' => $item]);
        exit;
    }

    // POST /api/seowriter/content
    if ($method === 'POST' && $path === 'content') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = $core->saveContent($data);
        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    // POST /api/seowriter/score (no credits — rule-based)
    if ($method === 'POST' && $path === 'score') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $editor = new SeoWriterEditor();
        $result = $editor->score(
            $data['title'] ?? '',
            $data['meta_description'] ?? '',
            $data['keyword'] ?? '',
            $data['body'] ?? ''
        );
        echo json_encode(['success' => true, 'result' => $result]);
        exit;
    }

    // POST /api/seowriter/generate (costs 5 credits)
    if ($method === 'POST' && $path === 'generate') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $credits = new SaasCredits();
        if (!$credits->hasCredits($userId, 'seowriter', 5)) {
            http_response_code(402);
            echo json_encode(['success' => false, 'error' => 'Insufficient credits (need 5)']);
            exit;
        }
        require_once CMS_ROOT . '/core/ai_content.php';
        $keyword = trim($data['keyword'] ?? '');
        $language = $data['language'] ?? 'en';
        $outline = trim($data['outline'] ?? '');
        $tone = $data['tone'] ?? 'professional';

        $prompt = "You are an expert SEO content writer. Write a comprehensive, SEO-optimized article.\n\n";
        $prompt .= "TARGET KEYWORD: {$keyword}\n";
        $prompt .= "LANGUAGE: {$language}\n";
        $prompt .= "TONE: {$tone}\n";
        if ($outline) $prompt .= "OUTLINE:\n{$outline}\n";
        $prompt .= "\nWrite a complete article with:\n- Engaging H1 title (include keyword)\n- Meta description (120-160 chars)\n- 1500+ words, structured with H2/H3 headings\n- Natural keyword placement (0.5-2.5% density)\n- Internal link suggestions [LINK: anchor text]\n- FAQ section at the end\n\nFormat: ## Title, ## Meta Description, ## Article";

        $aiResult = ai_content_generate(['topic' => $prompt, 'language' => $language, 'tone' => $tone, 'length_hint' => 'long']);

        if (!$aiResult['ok']) {
            echo json_encode(['success' => false, 'error' => $aiResult['error'] ?? 'AI generation failed']);
            exit;
        }

        $credits->consume($userId, 'seowriter', 5, 'Article generation: ' . $keyword);

        // Parse response
        $content = $aiResult['content'] ?? '';
        $title = '';
        $metaDesc = '';
        $body = $content;
        if (preg_match('/##\s*Title\s*\n+(.+)/i', $content, $tm)) $title = trim($tm[1]);
        if (preg_match('/##\s*Meta\s*Description\s*\n+(.+)/i', $content, $mm)) $metaDesc = trim($mm[1]);
        if (preg_match('/##\s*Article\s*\n+(.*)/is', $content, $am)) $body = trim($am[1]);

        $wordCount = str_word_count(strip_tags($body));
        $editor = new SeoWriterEditor();
        $score = $editor->score($title, $metaDesc, $keyword, $body);

        $id = $core->saveContent([
            'project_id' => $data['project_id'] ?? null,
            'title' => $title,
            'meta_description' => $metaDesc,
            'target_keyword' => $keyword,
            'body' => $body,
            'seo_score' => $score['score'],
            'word_count' => $wordCount,
            'status' => 'complete',
        ]);

        echo json_encode([
            'success' => true,
            'id' => $id,
            'title' => $title,
            'meta_description' => $metaDesc,
            'body' => $body,
            'seo_score' => $score['score'],
            'word_count' => $wordCount,
            'checks' => $score['checks'],
        ]);
        exit;
    }

    // POST /api/seowriter/keywords (costs 2 credits)
    if ($method === 'POST' && $path === 'keywords') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $credits = new SaasCredits();
        if (!$credits->hasCredits($userId, 'seowriter', 2)) {
            http_response_code(402);
            echo json_encode(['success' => false, 'error' => 'Insufficient credits (need 2)']);
            exit;
        }
        $kw = new SeoWriterKeyword();
        $result = $kw->research($data['keyword'] ?? '', $data['language'] ?? 'en', $data['niche'] ?? '');
        if ($result['success']) {
            $credits->consume($userId, 'seowriter', 2, 'Keyword research: ' . ($data['keyword'] ?? ''));
        }
        echo json_encode($result);
        exit;
    }

    // POST /api/seowriter/audit
    if ($method === 'POST' && $path === 'audit') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = $core->saveAudit(
            $data['url'] ?? '',
            (int)($data['score'] ?? 0),
            $data['issues'] ?? [],
            $data['meta'] ?? [],
            $data['project_id'] ?? null
        );
        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    // GET /api/seowriter/audits
    if ($method === 'GET' && $path === 'audits') {
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
        echo json_encode(['success' => true, 'audits' => $core->getAudits($limit)]);
        exit;
    }

    // 404
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found: ' . $path]);

} catch (\Throwable $e) {
    error_log('[SeoWriter API] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
