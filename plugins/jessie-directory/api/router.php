<?php
/**
 * Jessie Directory — API Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-directory-listing.php';
require_once $pluginDir . '/includes/class-directory-category.php';
require_once $pluginDir . '/includes/class-directory-review.php';
require_once $pluginDir . '/includes/class-directory-ai.php';

header('Content-Type: application/json');

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/directory/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        case 'listings':
            echo json_encode(['ok' => true] + \DirectoryListing::getAll(array_merge($_GET, ['status' => 'active'])));
            exit;

        case 'listing':
            if ($id) {
                $listing = \DirectoryListing::get($id);
                if ($listing) \DirectoryListing::incrementViews($id);
                $reviews = $listing ? \DirectoryReview::getForListing($id) : [];
                echo json_encode(['ok' => (bool)$listing, 'listing' => $listing, 'reviews' => $reviews]);
                exit;
            }
            break;

        case 'categories':
            echo json_encode(['ok' => true, 'categories' => \DirectoryCategory::getTree()]);
            exit;

        case 'search':
            $q = $_GET['q'] ?? '';
            echo json_encode(['ok' => true] + \DirectoryListing::getAll(['search' => $q, 'status' => 'active']));
            exit;

        case 'review':
            if ($method !== 'POST') break;
            $reviewId = \DirectoryReview::create($input);
            echo json_encode(['ok' => true, 'review_id' => $reviewId, 'message' => 'Review submitted for moderation.']);
            exit;

        case 'submit':
            if ($method !== 'POST') break;
            $data = $input; $data['status'] = 'pending';
            $listingId = \DirectoryListing::create($data);
            echo json_encode(['ok' => true, 'listing_id' => $listingId, 'message' => 'Listing submitted for review.']);
            exit;

        // ADMIN
        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true, 'stats' => \DirectoryListing::getStats()]);
            exit;

        case 'pending-reviews':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true, 'reviews' => \DirectoryReview::getPending()]);
            exit;

        case 'approve-review':
            if (!$isAdmin || !$id) break;
            \DirectoryReview::approve($id); echo json_encode(['ok' => true]); exit;

        case 'reject-review':
            if (!$isAdmin || !$id) break;
            \DirectoryReview::reject($id); echo json_encode(['ok' => true]); exit;

        // AI
        case 'ai-description':

        // Claims
        case 'approve-claim':
            if (!$isAdmin || !$id) break;
            $pdo = db();
            $pdo->prepare("UPDATE directory_claims SET status = 'approved' WHERE id = ?")->execute([$id]);
            $stmt = $pdo->prepare("SELECT listing_id FROM directory_claims WHERE id = ?"); $stmt->execute([$id]); $lid = (int)$stmt->fetchColumn();
            if ($lid) $pdo->prepare("UPDATE directory_listings SET is_claimed = 1 WHERE id = ?")->execute([$lid]);
            echo json_encode(['ok' => true]); exit;

        case 'reject-claim':
            if (!$isAdmin || !$id) break;
            db()->prepare("UPDATE directory_claims SET status = 'rejected' WHERE id = ?")->execute([$id]);
            echo json_encode(['ok' => true]); exit;
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(\DirectoryAI::generateDescription($input['name'] ?? '', $input['category'] ?? '', $input['city'] ?? '', $input['language'] ?? 'en'));
            exit;

        case 'ai-optimize':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(\DirectoryAI::optimizeListing((int)($input['listing_id'] ?? $id ?? 0)));
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
