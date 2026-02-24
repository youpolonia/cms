<?php
/**
 * Jessie Affiliate — API Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-affiliate-program.php';
require_once $pluginDir . '/includes/class-affiliate.php';
require_once $pluginDir . '/includes/class-affiliate-ai.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/affiliate/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        // ─── PUBLIC: List active programs ───
        case 'programs':
            if ($method === 'GET') {
                $programs = \AffiliateProgram::getActive();
                echo json_encode(['ok' => true, 'programs' => $programs]);
                exit;
            }
            break;

        // ─── PUBLIC: Register as affiliate ───
        case 'register':
            if ($method !== 'POST') break;
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $programId = (int)($input['program_id'] ?? 0);
            if (!$name || !$email || !$programId) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Name, email, and program are required.']);
                exit;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Invalid email address.']);
                exit;
            }
            // Check if already registered
            $existing = \Affiliate::getByEmail($email);
            if ($existing) {
                http_response_code(409);
                echo json_encode(['ok' => false, 'error' => 'This email is already registered as an affiliate.']);
                exit;
            }
            $program = \AffiliateProgram::get($programId);
            if (!$program || $program['status'] !== 'active') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Invalid or inactive program.']);
                exit;
            }
            $affId = \Affiliate::create([
                'program_id' => $programId,
                'name' => $name,
                'email' => $email,
                'website' => ($input['website'] ?? null) ?: '',
                'payment_method' => ($input['payment_method'] ?? null) ?: '',
                'payment_details' => ($input['payment_details'] ?? null) ?: '',
            ]);
            $aff = \Affiliate::get($affId);
            echo json_encode(['ok' => true, 'affiliate_id' => $affId, 'referral_code' => $aff['referral_code'], 'status' => $aff['status'], 'message' => $aff['status'] === 'active' ? 'Welcome! Your affiliate account is active.' : 'Application submitted. Pending approval.']);
            exit;

        // ─── PUBLIC: Track click (GET ?ref=CODE) ───
        case 'track':
            if ($method !== 'GET') break;
            $code = trim($_GET['ref'] ?? '');
            if (!$code) {
                echo json_encode(['ok' => false, 'error' => 'Missing ref code.']);
                exit;
            }
            $affiliate = \Affiliate::getByCode($code);
            if (!$affiliate) {
                echo json_encode(['ok' => false, 'error' => 'Invalid referral code.']);
                exit;
            }
            \Affiliate::trackClick($code);
            $cookieDays = (int)($affiliate['cookie_days'] ?? 30);
            setcookie('aff_ref', $code, time() + ($cookieDays * 86400), '/', '', false, true);
            echo json_encode(['ok' => true, 'message' => 'Click tracked.', 'cookie_days' => $cookieDays]);
            exit;

        // ─── ADMIN: Record conversion ───
        case 'convert':
            if ($method !== 'POST' || !$isAdmin) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Unauthorized']); exit; }
            $affiliateId = (int)($input['affiliate_id'] ?? 0);
            $orderId = ($input['order_id'] ?? null) ?: '';
            $orderTotal = (float)($input['order_total'] ?? 0);
            if (!$affiliateId) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'affiliate_id required']);
                exit;
            }
            $aff = \Affiliate::get($affiliateId);
            if (!$aff) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Affiliate not found']);
                exit;
            }
            $program = \AffiliateProgram::get((int)$aff['program_id']);
            $commission = 0;
            if ($program) {
                $commission = $program['commission_type'] === 'percentage'
                    ? round($orderTotal * (float)$program['commission_value'] / 100, 2)
                    : (float)$program['commission_value'];
            }
            if (!empty($input['commission'])) $commission = (float)$input['commission'];
            $convId = \Affiliate::recordConversion($affiliateId, (int)$aff['program_id'], $orderId, $orderTotal, $commission);
            echo json_encode(['ok' => true, 'conversion_id' => $convId, 'commission' => $commission]);
            exit;

        // ─── ADMIN: Stats ───
        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true, 'stats' => \AffiliateProgram::getStats()]);
            exit;

        // ─── ADMIN: Payouts list ───
        case 'payouts':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true] + \Affiliate::getPayouts($_GET));
            exit;

        // ─── AI: Generate promotional content ───
        case 'ai-promo':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            $programName = $input['program_name'] ?? '';
            $commissionInfo = ($input['commission_info'] ?? null) ?: '';
            $language = ($input['language'] ?? null) ?: 'en';
            $type = ($input['type'] ?? null) ?: 'social';
            if (!$programName) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'program_name required']);
                exit;
            }
            echo json_encode(\AffiliateAI::generatePromo($programName, $commissionInfo, $language, $type));
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
