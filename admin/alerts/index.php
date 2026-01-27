<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();
require_once CMS_ROOT . '/includes/systemalert.php';
require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$alerts = SystemAlert::get_alerts(null, $limit, $offset);
$total_alerts = count(SystemAlert::get_alerts(null, 0, 0));
$total_pages = $total_alerts > 0 ? ceil($total_alerts / $limit) : 1;


<div class="admin-container">
    <h1>System Alerts</h1>

    <?php if (empty($alerts)):
        <p>No active alerts.</p>
    <?php else:
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Message</th>
                    <th>Created</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alerts as $alert):
                <tr>
                    <td><?php echo htmlspecialchars((string)$alert['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($alert['type'] ?? 'info'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($alert['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($alert['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>Unresolved</td>
                    <td>
                        <form method="post" action="resolve.php" style="display:inline;">
                            <?php csrf_field();
                            <input type="hidden" name="alert_id" value="<?php echo htmlspecialchars((string)$alert['id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-sm">Resolve</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach;
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($page > 1):
                <a href="?page=<?php echo $page - 1; ?>" class="btn">Previous</a>
            <?php endif;
            <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <?php if ($page < $total_pages):
                <a href="?page=<?php echo $page + 1; ?>" class="btn">Next</a>
            <?php endif;
        </div>
    <?php endif;
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
