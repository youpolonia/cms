<?php
/**
 * Admin Email Queue Manager (Read-Only)
 *
 * Provides overview of queued emails with filtering and pagination.
 * This is a read-only view - no retry/delete/send actions in this version.
 */

// Step 1: Bootstrap
require_once __DIR__ . '/../../config.php';

// Step 2: DEV_MODE gate
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Step 3: Session
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

// Step 4: Permissions
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();

// Step 5: CSRF
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot();

// Step 6: Database
require_once __DIR__ . '/../../core/database.php';
$db = \core\Database::connection();

// Escaping helper
function esc($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Check if email_queue table exists
$tableExists = false;
$tableName = 'email_queue';
try {
    $db->query("SELECT 1 FROM {$tableName} LIMIT 1");
    $tableExists = true;
} catch (Exception $e) {
    $tableExists = false;
}

// Pagination settings
$perPage = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Search and filter params
$searchQuery = isset($_GET['q']) ? trim(substr($_GET['q'], 0, 200)) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Known statuses (adjust based on actual implementation)
$knownStatuses = ['pending', 'processing', 'sent', 'failed'];
if (!in_array($statusFilter, array_merge(['all'], $knownStatuses), true)) {
    $statusFilter = 'all';
}

// Initialize variables
$emails = [];
$totalCount = 0;

// If table exists, fetch data
if ($tableExists) {
    try {
        // Build WHERE clause
        $whereClauses = [];
        $params = [];

        if ($searchQuery !== '') {
            $whereClauses[] = "(to_email LIKE :search OR subject LIKE :search)";
            $params['search'] = '%' . $searchQuery . '%';
        }

        if ($statusFilter !== 'all') {
            $whereClauses[] = "status = :status";
            $params['status'] = $statusFilter;
        }

        $whereSQL = count($whereClauses) > 0 ? ' WHERE ' . implode(' AND ', $whereClauses) : '';

        // Count total
        $countStmt = $db->prepare("SELECT COUNT(*) FROM {$tableName}{$whereSQL}");
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value);
        }
        $countStmt->execute();
        $totalCount = (int)$countStmt->fetchColumn();

        // Fetch paginated results
        $orderSQL = " ORDER BY created_at DESC";
        $limitSQL = " LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare("SELECT * FROM {$tableName}{$whereSQL}{$orderSQL}{$limitSQL}");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Email Queue Manager Error: ' . $e->getMessage());
        $emails = [];
        $totalCount = 0;
    }
}

// Calculate pagination
$totalPages = $totalCount > 0 ? (int)ceil($totalCount / $perPage) : 1;
$showingStart = $totalCount > 0 ? $offset + 1 : 0;
$showingEnd = min($offset + $perPage, $totalCount);

// Build query string for pagination
$queryParams = [];
if ($searchQuery !== '') {
    $queryParams['q'] = $searchQuery;
}
if ($statusFilter !== 'all') {
    $queryParams['status'] = $statusFilter;
}
$queryString = count($queryParams) > 0 ? '&' . http_build_query($queryParams) : '';

// Step 7: Layout
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';
?>

<main class="container">
    <h1>Email Queue Manager</h1>

    <?php
    // Feedback banners
    if (isset($_GET['retried']) && $_GET['retried'] === '1'):
    ?>
        <div class="card" style="border-left: 4px solid #27ae60; background: #f0fdf4; padding: 20px; margin: 20px 0;">
            <strong style="color: #27ae60;">✓ Success:</strong> Email has been marked for retry.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
        <div class="card" style="border-left: 4px solid #27ae60; background: #f0fdf4; padding: 20px; margin: 20px 0;">
            <strong style="color: #27ae60;">✓ Success:</strong> Email has been deleted from the queue.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['retry']) && $_GET['retry'] === 'not_allowed'): ?>
        <div class="card" style="border-left: 4px solid #f39c12; background: #fffbf0; padding: 20px; margin: 20px 0;">
            <strong style="color: #f39c12;">ℹ Info:</strong> This email has already been sent and cannot be retried.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'rate_limit'): ?>
        <div class="card" style="border-left: 4px solid #e74c3c; background: #fef2f2; padding: 20px; margin: 20px 0;">
            <strong style="color: #e74c3c;">✗ Error:</strong> Too many actions in a short time. Please wait and try again.
        </div>
    <?php endif; ?>

    <?php if (!$tableExists): ?>
        <!-- Table Missing Warning -->
        <div class="card" style="border-left: 4px solid #f39c12; background: #fffbf0; padding: 20px; margin: 20px 0;">
            <h2 style="margin-top: 0; color: #f39c12;">⚠ Database Table Missing</h2>
            <p>The <code>email_queue</code> table does not exist in the database.</p>
            <p>To enable email queue functionality, create the table using the following SQL:</p>
            <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto; border-radius: 4px;"><code>CREATE TABLE IF NOT EXISTS email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(255) NOT NULL,
    from_email VARCHAR(255) DEFAULT NULL,
    subject VARCHAR(500) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    last_error TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;</code></pre>
            <p><strong>Note:</strong> After creating the table, refresh this page to view queued emails.</p>
        </div>
    <?php else: ?>
        <!-- Search and Filter Form -->
        <div class="card" style="margin: 20px 0; padding: 20px;">
            <form method="GET" action="/admin/email-queue/" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
                <div style="flex: 1; min-width: 200px;">
                    <label for="search" style="display: block; margin-bottom: 4px; font-weight: 500;">Search</label>
                    <input
                        type="text"
                        id="search"
                        name="q"
                        value="<?= esc($searchQuery) ?>"
                        placeholder="Search by email or subject..."
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                    >
                </div>
                <div style="min-width: 150px;">
                    <label for="status" style="display: block; margin-bottom: 4px; font-weight: 500;">Status</label>
                    <select
                        id="status"
                        name="status"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                    >
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All</option>
                        <?php foreach ($knownStatuses as $status): ?>
                            <option value="<?= esc($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                                <?= esc(ucfirst($status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn primary">Filter</button>
                    <a href="/admin/email-queue/" class="btn">Clear</a>
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        <div style="margin: 16px 0; color: #666;">
            <?php if ($totalCount > 0): ?>
                Showing <?= $showingStart ?>–<?= $showingEnd ?> of <?= $totalCount ?> queued emails
            <?php else: ?>
                No queued emails found
            <?php endif; ?>
        </div>

        <?php if (count($emails) > 0): ?>
            <!-- Email Queue Table -->
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <thead>
                        <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                            <th style="padding: 12px; text-align: left;">ID</th>
                            <th style="padding: 12px; text-align: left;">To</th>
                            <th style="padding: 12px; text-align: left;">Subject</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Attempts</th>
                            <th style="padding: 12px; text-align: left;">Created</th>
                            <th style="padding: 12px; text-align: left;">Last Error</th>
                            <th style="padding: 12px; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emails as $email): ?>
                            <?php
                            // Determine status badge color
                            $statusColors = [
                                'pending' => '#f39c12',
                                'processing' => '#3498db',
                                'sent' => '#27ae60',
                                'failed' => '#e74c3c'
                            ];
                            $statusColor = $statusColors[$email['status'] ?? 'pending'] ?? '#95a5a6';

                            // Truncate last error
                            $lastError = isset($email['last_error']) && $email['last_error'] !== null ? $email['last_error'] : '';
                            $lastErrorDisplay = strlen($lastError) > 50 ? substr($lastError, 0, 50) . '...' : $lastError;
                            ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><?= esc($email['id'] ?? '') ?></td>
                                <td style="padding: 12px;"><?= esc($email['to_email'] ?? '') ?></td>
                                <td style="padding: 12px;"><?= esc($email['subject'] ?? '') ?></td>
                                <td style="padding: 12px;">
                                    <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; background: <?= $statusColor ?>; color: white;">
                                        <?= esc(ucfirst($email['status'] ?? 'unknown')) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px;"><?= esc($email['attempts'] ?? 0) ?></td>
                                <td style="padding: 12px;"><?= esc($email['created_at'] ?? '') ?></td>
                                <td style="padding: 12px; color: #e74c3c; font-size: 12px;">
                                    <?= $lastError !== '' ? esc($lastErrorDisplay) : '—' ?>
                                </td>
                                <td style="padding: 12px;">
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <?php if ($email['status'] !== 'sent'): ?>
                                            <!-- Retry Button -->
                                            <form method="post" action="/admin/email-queue/retry.php" style="margin: 0; display: inline;">
                                                <input type="hidden" name="id" value="<?= esc($email['id']) ?>">
                                                <?php csrf_field(); ?>
                                                <button
                                                    type="submit"
                                                    style="padding: 6px 12px; background: #17a2b8; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;"
                                                    onmouseover="this.style.background='#138496'"
                                                    onmouseout="this.style.background='#17a2b8'"
                                                >
                                                    Retry
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <!-- Delete Button -->
                                        <form method="post" action="/admin/email-queue/delete.php" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete this email?');">
                                            <input type="hidden" name="id" value="<?= esc($email['id']) ?>">
                                            <?php csrf_field(); ?>
                                            <button
                                                type="submit"
                                                style="padding: 6px 12px; background: #dc3545; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;"
                                                onmouseover="this.style.background='#c82333'"
                                                onmouseout="this.style.background='#dc3545'"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div style="margin: 20px 0; display: flex; gap: 8px; justify-content: center;">
                    <?php if ($page > 1): ?>
                        <a href="/admin/email-queue/?page=1<?= $queryString ?>" class="btn">First</a>
                        <a href="/admin/email-queue/?page=<?= $page - 1 ?><?= $queryString ?>" class="btn">Previous</a>
                    <?php endif; ?>

                    <span style="padding: 8px 16px; background: #f5f5f5; border-radius: 4px;">
                        Page <?= $page ?> of <?= $totalPages ?>
                    </span>

                    <?php if ($page < $totalPages): ?>
                        <a href="/admin/email-queue/?page=<?= $page + 1 ?><?= $queryString ?>" class="btn">Next</a>
                        <a href="/admin/email-queue/?page=<?= $totalPages ?><?= $queryString ?>" class="btn">Last</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="card" style="text-align: center; padding: 60px 20px; color: #95a5a6;">
                <div style="font-size: 48px; margin-bottom: 16px;">📧</div>
                <h3 style="margin: 0 0 8px 0; color: #7f8c8d;">No Emails Found</h3>
                <p style="margin: 0;">
                    <?php if ($searchQuery !== '' || $statusFilter !== 'all'): ?>
                        No queued emails match the current filters.
                    <?php else: ?>
                        No queued emails found.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
