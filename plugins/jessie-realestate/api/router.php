<?php
/**
 * Jessie Real Estate — API Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');
$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-realestate-property.php';
require_once $pluginDir . '/includes/class-realestate-agent.php';
require_once $pluginDir . '/includes/class-realestate-ai.php';

header('Content-Type: application/json');
$isAdmin = isset($_SESSION['admin_id']) && ($_SESSION['admin_role'] ?? '') === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/realestate/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        // ─── PUBLIC ───
        case 'properties':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));
            $filters = ['status' => 'active'];
            foreach (['property_type','listing_type','city','bedrooms_min','bathrooms_min','price_min','price_max','sort','featured'] as $k) {
                if (!empty($_GET[$k])) $filters[$k] = $_GET[$k];
            }
            $result = \RealEstateProperty::getAll($filters, $page, $perPage);
            echo json_encode(['ok' => true] + $result);
            exit;

        case 'property':
            if ($id) {
                $prop = \RealEstateProperty::get($id);
                if ($prop && $prop['status'] === 'active') {
                    \RealEstateProperty::incrementViews($id);
                    echo json_encode(['ok' => true, 'property' => $prop]);
                } else {
                    echo json_encode(['ok' => false, 'error' => 'Not found']);
                }
                exit;
            }
            break;

        case 'search':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $filters = ['status' => 'active', 'search' => $_GET['q'] ?? ''];
            foreach (['property_type','listing_type','city','bedrooms_min','bathrooms_min','price_min','price_max','sort'] as $k) {
                if (!empty($_GET[$k])) $filters[$k] = $_GET[$k];
            }
            echo json_encode(['ok' => true] + \RealEstateProperty::getAll($filters, $page));
            exit;

        case 'agents':
            $agents = \RealEstateAgent::getAll('active');
            echo json_encode(['ok' => true, 'agents' => $agents]);
            exit;

        case 'inquiry':
            if ($method === 'POST') {
                if (empty($input['property_id']) || empty($input['name']) || empty($input['email'])) {
                    echo json_encode(['ok' => false, 'error' => 'Property ID, name, and email are required']);
                    exit;
                }
                // Check property exists
                $prop = \RealEstateProperty::get((int)$input['property_id']);
                if (!$prop) {
                    echo json_encode(['ok' => false, 'error' => 'Property not found']);
                    exit;
                }
                $pdo = db();
                $stmt = $pdo->prepare("INSERT INTO re_inquiries (property_id, name, email, phone, message) VALUES (?,?,?,?,?)");
                $stmt->execute([
                    (int)$input['property_id'],
                    $input['name'],
                    $input['email'],
                    ($input['phone'] ?? null) ?: null,
                    ($input['message'] ?? null) ?: null,
                ]);
                $inquiryId = (int)$pdo->lastInsertId();
                if (function_exists('cms_event')) cms_event('realestate.inquiry.created', ['id' => $inquiryId, 'property_id' => (int)$input['property_id'], 'name' => $input['name']]);
                echo json_encode(['ok' => true, 'inquiry_id' => $inquiryId, 'message' => 'Inquiry submitted successfully']);
                exit;
            }
            break;

        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true, 'stats' => \RealEstateProperty::getStats()]);
            exit;

        // ─── AI ───
        case 'ai-description':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(\RealEstateAI::generateDescription(
                $input['title'] ?? '',
                $input['property_type'] ?? '',
                $input['city'] ?? '',
                (int)($input['bedrooms'] ?? 0),
                (float)($input['price'] ?? 0)
            ));
            exit;

        case 'ai-valuation':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(\RealEstateAI::generateValuation(
                $input['title'] ?? '',
                $input['property_type'] ?? '',
                $input['city'] ?? '',
                (int)($input['bedrooms'] ?? 0),
                (int)($input['bathrooms'] ?? 0),
                (int)($input['area_sqft'] ?? 0)
            ));
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
