<?php
/**
 * Jessie Membership — API Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-membership-plan.php';
require_once $pluginDir . '/includes/class-membership-member.php';
require_once $pluginDir . '/includes/class-membership-access.php';
require_once $pluginDir . '/includes/class-membership-ai.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/membership/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        // ─── PUBLIC: Plans ───
        case 'plans':
            echo json_encode(['ok' => true, 'plans' => \MembershipPlan::getPublicPlans()]);
            exit;

        // ─── PUBLIC: Sign up ───
        case 'signup':
            if ($method !== 'POST') break;
            $planId = (int)($input['plan_id'] ?? 0);
            $email = $input['email'] ?? '';
            if (!$planId || !$email) { echo json_encode(['ok' => false, 'error' => 'plan_id and email required']); exit; }
            $existing = \MembershipMember::getByEmail($email);
            if ($existing) { echo json_encode(['ok' => false, 'error' => 'Already a member', 'member' => $existing]); exit; }
            $memberId = \MembershipMember::create(['plan_id' => $planId, 'email' => $email, 'name' => $input['name'] ?? '']);
            echo json_encode(['ok' => true, 'member_id' => $memberId]);
            exit;

        // ─── PUBLIC: Check access ───
        case 'check-access':
            $userId = (int)($_GET['user_id'] ?? $_SESSION['user_id'] ?? 0);
            $contentType = $_GET['content_type'] ?? 'page';
            $contentId = (int)($_GET['content_id'] ?? 0);
            echo json_encode(['ok' => true, 'has_access' => \MembershipAccess::canAccess($userId, $contentType, $contentId)]);
            exit;

        // ─── ADMIN ───
        case 'members':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(['ok' => true] + \MembershipMember::getAll($_GET));
            exit;

        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(['ok' => true, 'stats' => \MembershipMember::getStats()]);
            exit;

        case 'expire-check':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $count = \MembershipMember::checkExpired();
            echo json_encode(['ok' => true, 'expired' => $count]);
            exit;

        // ─── AI ───
        case 'ai-plan':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\MembershipAI::generatePlanContent($input['name'] ?? '', (float)($input['price'] ?? 0), $input['billing'] ?? 'monthly', $input['industry'] ?? '', $input['language'] ?? 'en'));
            exit;

        case 'ai-pricing-page':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\MembershipAI::generatePricingPageCopy($input['language'] ?? 'en'));
            exit;

        case 'ai-churn':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            echo json_encode(\MembershipAI::analyzeChurnRisk());
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
