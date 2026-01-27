<?php
/**
 * Admin Notifications Center - Unified Read-Only Display
 * Shows both database notifications and JSON queue system notifications
 */

// Bootstrap (exact order as specified)
require_once __DIR__ . '/../../config.php';

// DEV_MODE gate
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

// Session boot
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

// RBAC
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();

// CSRF
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');

// Database
require_once __DIR__ . '/../../core/database.php';
$db = \core\Database::connection();

// Load notification managers
require_once __DIR__ . '/../../core/notificationmanager.php';
require_once __DIR__ . '/../../includes/system/notificationmanager.php';

// Helper function
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

// Pagination and filtering
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['q']) ? substr(trim($_GET['q']), 0, 200) : '';
$statusFilter = $_GET['status'] ?? 'all';
$sourceFilter = $_GET['source'] ?? 'all'; // 'database', 'queue', 'all'

// Validate filters
$allowedStatuses = ['all', 'read', 'unread', 'info', 'warning', 'error', 'system'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}
$allowedSources = ['all', 'database', 'queue'];
if (!in_array($sourceFilter, $allowedSources, true)) {
    $sourceFilter = 'all';
}

// Check if notifications table exists
$tableExists = false;
try {
    $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = ($stmt->rowCount() > 0);
} catch (PDOException $e) {
    $tableExists = false;
}

// Collect notifications from both sources
$allNotifications = [];

// 1. Database notifications (if table exists)
if ($tableExists && ($sourceFilter === 'all' || $sourceFilter === 'database')) {
    try {
        $sql = "SELECT id, user_id, type, message, is_read, created_at, 'database' as source
                FROM notifications
                WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (message LIKE ? OR type LIKE ?)";
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if ($statusFilter === 'read') {
            $sql .= " AND is_read = 1";
        } elseif ($statusFilter === 'unread') {
            $sql .= " AND is_read = 0";
        } elseif (in_array($statusFilter, ['info', 'warning', 'error', 'system'])) {
            $sql .= " AND type = ?";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $dbNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dbNotifications as $notif) {
            $allNotifications[] = [
                'id' => 'db_' . $notif['id'],
                'db_id' => (int)$notif['id'], // Store raw DB id for actions
                'source' => 'database',
                'type' => $notif['type'],
                'message' => $notif['message'],
                'is_read' => (bool)$notif['is_read'],
                'timestamp' => strtotime($notif['created_at']),
                'created_at' => $notif['created_at'],
                'user_id' => $notif['user_id'],
                'context' => []
            ];
        }
    } catch (PDOException $e) {
        // Silent fail - continue with queue notifications
    }
}

// 2. JSON Queue notifications (system-wide)
if ($sourceFilter === 'all' || $sourceFilter === 'queue') {
    $queueNotifications = \NotificationManager::getQueuedNotifications();

    foreach ($queueNotifications as $notif) {
        // Apply filters
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $messageLower = strtolower($notif['message'] ?? '');
            $typeLower = strtolower($notif['type'] ?? '');
            if (strpos($messageLower, $searchLower) === false && strpos($typeLower, $searchLower) === false) {
                continue;
            }
        }

        if ($statusFilter !== 'all' && $notif['type'] !== $statusFilter) {
            if (!($statusFilter === 'read' && !empty($notif['read']))) {
                if (!($statusFilter === 'unread' && empty($notif['read']))) {
                    continue;
                }
            }
        }

        $allNotifications[] = [
            'id' => 'queue_' . ($notif['id'] ?? uniqid()),
            'db_id' => null, // Queue notifications have no DB id
            'source' => 'queue',
            'type' => $notif['type'] ?? 'info',
            'message' => $notif['message'] ?? '',
            'is_read' => !empty($notif['read']),
            'timestamp' => $notif['timestamp'] ?? time(),
            'created_at' => date('Y-m-d H:i:s', $notif['timestamp'] ?? time()),
            'user_id' => null,
            'context' => $notif['context'] ?? []
        ];
    }
}

// Sort by timestamp DESC
usort($allNotifications, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

// Pagination
$totalNotifications = count($allNotifications);
$totalPages = max(1, (int)ceil($totalNotifications / $perPage));
$page = min($page, $totalPages);
$paginatedNotifications = array_slice($allNotifications, $offset, $perPage);

// Build query string for pagination
$queryParams = [];
if (!empty($search)) $queryParams['q'] = $search;
if ($statusFilter !== 'all') $queryParams['status'] = $statusFilter;
if ($sourceFilter !== 'all') $queryParams['source'] = $sourceFilter;

// Load header and navigation
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';
?>

<div class="admin-content">
    <div class="content-header">
        <h1>Notifications Center</h1>
        <p>Unified view of all system and user notifications</p>
    </div>

    <?php if (isset($_GET['marked']) && $_GET['marked'] === '1'): ?>
        <div class="alert alert-success">
            <strong>Success:</strong> Notification has been marked as read.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <?php
        $errorMessages = [
            'rate_limit' => 'Rate limit exceeded. Please wait before marking more notifications.',
            'no_table' => 'The notifications table does not exist in the database.',
            'no_is_read' => 'The notifications table is missing the is_read column.',
            'db' => 'A database error occurred. Please try again.'
        ];
        $errorMsg = $errorMessages[$_GET['error']] ?? 'An error occurred.';
        ?>
        <div class="alert alert-error">
            <strong>Error:</strong> <?= esc($errorMsg) ?>
        </div>
    <?php endif; ?>

    <?php if (!$tableExists): ?>
        <div class="alert alert-warning">
            <strong>Database Table Missing:</strong> The <code>notifications</code> table does not exist.
            Only showing queue-based notifications. To enable database notifications, run:
            <pre style="background: #f5f5f5; padding: 10px; margin-top: 10px;">
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            </pre>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filters-section" style="background: #f9f9f9; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
        <form method="get" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div>
                <label for="q" style="display: block; margin-bottom: 5px; font-weight: 600;">Search:</label>
                <input type="text" id="q" name="q" value="<?= esc($search) ?>"
                       placeholder="Search message or type..."
                       style="padding: 8px; width: 250px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div>
                <label for="source" style="display: block; margin-bottom: 5px; font-weight: 600;">Source:</label>
                <select id="source" name="source" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="all"<?= $sourceFilter === 'all' ? ' selected' : '' ?>>All Sources</option>
                    <option value="database"<?= $sourceFilter === 'database' ? ' selected' : '' ?>>Database (User)</option>
                    <option value="queue"<?= $sourceFilter === 'queue' ? ' selected' : '' ?>>Queue (System)</option>
                </select>
            </div>

            <div>
                <label for="status" style="display: block; margin-bottom: 5px; font-weight: 600;">Status/Type:</label>
                <select id="status" name="status" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="all"<?= $statusFilter === 'all' ? ' selected' : '' ?>>All</option>
                    <option value="unread"<?= $statusFilter === 'unread' ? ' selected' : '' ?>>Unread</option>
                    <option value="read"<?= $statusFilter === 'read' ? ' selected' : '' ?>>Read</option>
                    <option value="info"<?= $statusFilter === 'info' ? ' selected' : '' ?>>Info</option>
                    <option value="warning"<?= $statusFilter === 'warning' ? ' selected' : '' ?>>Warning</option>
                    <option value="error"<?= $statusFilter === 'error' ? ' selected' : '' ?>>Error</option>
                    <option value="system"<?= $statusFilter === 'system' ? ' selected' : '' ?>>System</option>
                </select>
            </div>

            <div>
                <button type="submit" style="padding: 8px 20px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                    Apply Filters
                </button>
                <?php if (!empty($search) || $statusFilter !== 'all' || $sourceFilter !== 'all'): ?>
                    <a href="/admin/notifications/" style="margin-left: 10px; padding: 8px 15px; background: #666; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Stats Summary -->
    <div class="stats-summary" style="display: flex; gap: 15px; margin-bottom: 20px;">
        <div style="flex: 1; background: #e3f2fd; padding: 15px; border-radius: 4px; border-left: 4px solid #2196f3;">
            <div style="font-size: 24px; font-weight: bold; color: #1976d2;"><?= $totalNotifications ?></div>
            <div style="color: #666; font-size: 14px;">Total Notifications</div>
        </div>
        <div style="flex: 1; background: #fff3e0; padding: 15px; border-radius: 4px; border-left: 4px solid #ff9800;">
            <div style="font-size: 24px; font-weight: bold; color: #f57c00;"><?= count(array_filter($allNotifications, fn($n) => !$n['is_read'])) ?></div>
            <div style="color: #666; font-size: 14px;">Unread</div>
        </div>
        <div style="flex: 1; background: #f3e5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #9c27b0;">
            <div style="font-size: 24px; font-weight: bold; color: #7b1fa2;"><?= count(array_filter($allNotifications, fn($n) => $n['source'] === 'database')) ?></div>
            <div style="color: #666; font-size: 14px;">From Database</div>
        </div>
        <div style="flex: 1; background: #e8f5e9; padding: 15px; border-radius: 4px; border-left: 4px solid #4caf50;">
            <div style="font-size: 24px; font-weight: bold; color: #388e3c;"><?= count(array_filter($allNotifications, fn($n) => $n['source'] === 'queue')) ?></div>
            <div style="color: #666; font-size: 14px;">From Queue</div>
        </div>
    </div>

    <!-- Notifications List -->
    <?php if (empty($paginatedNotifications)): ?>
        <div class="no-results" style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 4px;">
            <p style="font-size: 18px; color: #666;">No notifications found.</p>
            <?php if (!empty($search) || $statusFilter !== 'all' || $sourceFilter !== 'all'): ?>
                <p><a href="/admin/notifications/">Clear filters</a> to see all notifications.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($paginatedNotifications as $notification): ?>
                <?php
                $typeColors = [
                    'info' => ['bg' => '#e3f2fd', 'border' => '#2196f3', 'text' => '#1565c0'],
                    'warning' => ['bg' => '#fff3e0', 'border' => '#ff9800', 'text' => '#e65100'],
                    'error' => ['bg' => '#ffebee', 'border' => '#f44336', 'text' => '#c62828'],
                    'system' => ['bg' => '#e8f5e9', 'border' => '#4caf50', 'text' => '#2e7d32']
                ];
                $colors = $typeColors[$notification['type']] ?? $typeColors['info'];
                $isUnread = !$notification['is_read'];
                ?>
                <div class="notification-item" style="
                    background: <?= $colors['bg'] ?>;
                    border-left: 5px solid <?= $colors['border'] ?>;
                    padding: 15px;
                    margin-bottom: 15px;
                    border-radius: 4px;
                    <?= $isUnread ? 'box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : '' ?>
                ">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <span style="
                                display: inline-block;
                                padding: 4px 10px;
                                background: <?= $colors['border'] ?>;
                                color: white;
                                border-radius: 3px;
                                font-size: 12px;
                                font-weight: 600;
                                text-transform: uppercase;
                            "><?= esc($notification['type']) ?></span>

                            <span style="
                                display: inline-block;
                                padding: 4px 10px;
                                background: #<?= $notification['source'] === 'database' ? '9c27b0' : '4caf50' ?>;
                                color: white;
                                border-radius: 3px;
                                font-size: 11px;
                                font-weight: 600;
                            "><?= esc(strtoupper($notification['source'])) ?></span>

                            <?php if ($isUnread): ?>
                                <span style="
                                    display: inline-block;
                                    padding: 4px 10px;
                                    background: #ff5722;
                                    color: white;
                                    border-radius: 3px;
                                    font-size: 11px;
                                    font-weight: 600;
                                ">UNREAD</span>
                            <?php endif; ?>
                        </div>

                        <div style="text-align: right; color: #666; font-size: 13px;">
                            <div><?= esc($notification['created_at']) ?></div>
                            <?php if ($notification['user_id']): ?>
                                <div style="font-size: 11px; margin-top: 2px;">User ID: <?= esc($notification['user_id']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="color: <?= $colors['text'] ?>; font-size: 15px; line-height: 1.5; <?= $isUnread ? 'font-weight: 600;' : '' ?>">
                        <?= esc($notification['message']) ?>
                    </div>

                    <?php if (!empty($notification['context']) && is_array($notification['context'])): ?>
                        <div style="margin-top: 10px; padding: 10px; background: rgba(255,255,255,0.7); border-radius: 3px; font-size: 13px; color: #666;">
                            <strong>Context:</strong>
                            <?php foreach ($notification['context'] as $key => $value): ?>
                                <span style="margin-left: 10px;">
                                    <strong><?= esc($key) ?>:</strong> <?= esc(is_array($value) ? json_encode($value) : $value) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 11px; color: #999;">
                            ID: <?= esc($notification['id']) ?>
                        </div>

                        <?php if ($notification['source'] === 'database' && !$notification['is_read'] && $notification['db_id']): ?>
                            <form method="post" action="/admin/notifications/mark-read.php" style="margin: 0;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= (int)$notification['db_id'] ?>">
                                <button type="submit" style="
                                    padding: 6px 12px;
                                    background: #4caf50;
                                    color: white;
                                    border: none;
                                    border-radius: 3px;
                                    cursor: pointer;
                                    font-size: 12px;
                                    font-weight: 600;
                                ">Mark as Read</button>
                            </form>
                        <?php elseif ($notification['source'] === 'database' && $notification['is_read']): ?>
                            <span style="
                                padding: 6px 12px;
                                background: #e0e0e0;
                                color: #666;
                                border-radius: 3px;
                                font-size: 12px;
                                font-weight: 600;
                            ">Read</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination" style="margin-top: 30px; text-align: center;">
                <div style="display: inline-flex; gap: 5px; background: #f5f5f5; padding: 10px; border-radius: 4px;">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $page - 1])) ?>"
                           style="padding: 8px 12px; background: white; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 3px;">
                            &laquo; Previous
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    if ($startPage > 1): ?>
                        <a href="?<?= http_build_query(array_merge($queryParams, ['page' => 1])) ?>"
                           style="padding: 8px 12px; background: white; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 3px;">1</a>
                        <?php if ($startPage > 2): ?>
                            <span style="padding: 8px 12px;">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span style="padding: 8px 12px; background: #0066cc; color: white; font-weight: bold; border-radius: 3px;">
                                <?= $i ?>
                            </span>
                        <?php else: ?>
                            <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>"
                               style="padding: 8px 12px; background: white; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 3px;">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span style="padding: 8px 12px;">...</span>
                        <?php endif; ?>
                        <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $totalPages])) ?>"
                           style="padding: 8px 12px; background: white; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 3px;">
                            <?= $totalPages ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $page + 1])) ?>"
                           style="padding: 8px 12px; background: white; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 3px;">
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 10px; color: #666; font-size: 14px;">
                    Page <?= $page ?> of <?= $totalPages ?> (<?= $totalNotifications ?> total notifications)
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.admin-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.content-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

.content-header h1 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 28px;
}

.content-header p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #28a745;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f44336;
    color: #721c24;
}

.alert strong {
    font-weight: 600;
}

pre {
    overflow-x: auto;
    font-size: 12px;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php';
