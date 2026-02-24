<?php
/**
 * Jessie Newsletter+ — API Router
 * Handles /api/newsletter/* requests
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-newsletter-list.php';
require_once $pluginDir . '/includes/class-newsletter-subscriber.php';
require_once $pluginDir . '/includes/class-newsletter-campaign.php';
require_once $pluginDir . '/includes/class-newsletter-sender.php';
require_once $pluginDir . '/includes/class-newsletter-ai.php';

header('Content-Type: application/json');

$isAdmin = false;
if (function_exists('\\Core\\Session::isLoggedIn')) {
    $isAdmin = \Core\Session::isLoggedIn() && (\Core\Session::get('role') === 'admin');
} elseif (isset($_SESSION['user_role'])) {
    $isAdmin = $_SESSION['user_role'] === 'admin';
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/newsletter/([\w-]+)(?:/(\w+))?(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $sub = $m[2] ?? null;
    $id = isset($m[3]) ? (int)$m[3] : (is_numeric($sub) ? (int)$sub : null);
    if (is_numeric($sub)) $sub = null;

    switch ($endpoint) {
        // ─── PUBLIC: Subscribe ───
        case 'subscribe':
            if ($method !== 'POST') { echo json_encode(['ok' => false, 'error' => 'POST required']); exit; }
            $email = $input['email'] ?? '';
            $name = $input['name'] ?? '';
            $listId = (int)($input['list_id'] ?? 0);
            $result = \NewsletterSubscriber::subscribe($email, $name, $listId ? [$listId] : [], 'form');
            echo json_encode($result);
            exit;

        // ─── PUBLIC: Unsubscribe ───
        case 'unsubscribe':
            $email = $_GET['email'] ?? $input['email'] ?? '';
            $result = \NewsletterSubscriber::unsubscribe($email);
            echo json_encode($result);
            exit;

        // ─── PUBLIC: Track open (pixel) ───
        case 'track':
            if ($sub === 'open') {
                $cid = (int)($_GET['cid'] ?? 0);
                $sid = (int)($_GET['sid'] ?? 0);
                if ($cid && $sid) \NewsletterSender::trackOpen($cid, $sid);
                header('Content-Type: image/gif');
                echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
                exit;
            }
            if ($sub === 'click') {
                $cid = (int)($_GET['cid'] ?? 0);
                $sid = (int)($_GET['sid'] ?? 0);
                $url = $_GET['url'] ?? '';
                if ($cid && $sid) \NewsletterSender::trackClick($cid, $sid, $url);
                header('Location: ' . ($url ?: '/'));
                exit;
            }
            break;

        // ─── ADMIN: Subscribers ───
        case 'subscribers':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            if ($method === 'GET') {
                echo json_encode(['ok' => true] + \NewsletterSubscriber::getAll($_GET));
                exit;
            }
            if ($method === 'POST' && $sub === 'import') {
                $csv = $input['csv'] ?? '';
                $listId = (int)($input['list_id'] ?? 0);
                echo json_encode(\NewsletterSubscriber::importCSV($csv, $listId));
                exit;
            }
            if ($method === 'POST' && $id) {
                \NewsletterSubscriber::delete($id);
                echo json_encode(['ok' => true]);
                exit;
            }
            break;

        // ─── ADMIN: Lists ───
        case 'lists':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(['ok' => true, 'lists' => \NewsletterList::getAll()]);
            exit;

        // ─── ADMIN: Campaigns ───
        case 'campaigns':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            if ($method === 'GET') {
                echo json_encode(['ok' => true] + \NewsletterCampaign::getAll($_GET));
                exit;
            }
            break;

        // ─── ADMIN: Send campaign ───
        case 'send':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $campaignId = (int)($input['campaign_id'] ?? $id ?? 0);
            echo json_encode(\NewsletterSender::send($campaignId));
            exit;

        case 'send-test':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $campaignId = (int)($input['campaign_id'] ?? 0);
            $testEmail = $input['email'] ?? '';
            echo json_encode(\NewsletterSender::sendTest($campaignId, $testEmail));
            exit;

        case 'process-scheduled':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\NewsletterSender::processScheduled());
            exit;

        // ─── ADMIN: Stats ───
        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(['ok' => true, 'subscribers' => \NewsletterSubscriber::getStats(), 'campaigns' => \NewsletterCampaign::getStats()]);
            exit;

        // ─── AI Endpoints ───
        case 'ai-subjects':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\NewsletterAI::generateSubjectLines($input['topic'] ?? '', $input['tone'] ?? 'professional', $input['language'] ?? 'en'));
            exit;

        case 'ai-content':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\NewsletterAI::generateContent($input['brief'] ?? '', $input['template'] ?? 'promotional', $input['tone'] ?? 'friendly', $input['language'] ?? 'en'));
            exit;

        case 'ai-improve':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\NewsletterAI::improveContent($input['content_html'] ?? '', $input['goal'] ?? 'engagement'));
            exit;

        case 'ai-send-time':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\NewsletterAI::suggestSendTime());
            exit;

        case 'ai-analyze':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\NewsletterAI::analyzeCampaign((int)($input['campaign_id'] ?? $id ?? 0)));
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
