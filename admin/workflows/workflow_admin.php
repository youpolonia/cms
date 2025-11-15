<?php
$app = require_once __DIR__ . '/../../includes/bootstrap.php';

// Database connection with error handling
try {
    $pdo = $app->get('db');
    // Test connection immediately
    $pdo->query("SELECT 1");
} catch (Exception $e) {
    $errorId = uniqid('DB_CONN_');
    error_log(sprintf(
        "[%s] Database connection failed - %s\n%s",
        $errorId,
        $e->getMessage(),
        $e->getTraceAsString()
    ));
    http_response_code(503);
    die(sprintf(
        '
<div class="alert alert-danger">'
        . 'Database connection failed (Error ID: %s). Please try again later.'
        . '</div>',
        htmlspecialchars(
$errorId)
    ));
}

// Base query
$query = "SELECT 
            h.transitioned_at,
            c.title AS content_title,
            ws_from.name AS from_state,
            ws_to.name AS to_state,
            u.username AS user_name,
            h.notes
          FROM content_workflow_history h
          LEFT JOIN contents c ON h.content_id = c.id
          LEFT JOIN workflow_states ws_from ON h.from_workflow_state_id = ws_from.id
          LEFT JOIN workflow_states ws_to ON h.to_workflow_state_id = ws_to.id
          LEFT JOIN users u ON h.user_id = u.id";

// Filter handling
$where = [];
$params = [];

if (!empty($_GET['start_date'])) {
    $where[] = "h.transitioned_at >= :start_date";
    $params[':start_date'] = $_GET['start_date'];
}

if (!empty($_GET['end_date'])) {
    $where[] = "h.transitioned_at <= :end_date";
    $params[':end_date'] = $_GET['end_date'];
}

if (!empty($_GET['state'])) {
    $where[] = "ws_to.name = :state";
    $params[':state'] = $_GET['state'];
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY h.transitioned_at DESC LIMIT 100";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $transitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorId = uniqid('QUERY_');
    error_log(sprintf(
        "[%s] Workflow query failed - Query: %s\nError: %s\nTrace:\n%s",
        $errorId,
        $query,
        $e->getMessage(),
        $e->getTraceAsString()
    ));
    $transitions = [];
    $error = sprintf(
        '
<div class="alert alert-warning">'
        . 'Unable to load full workflow history (Error ID: %s). Showing partial results.'
        . '</div>',
        htmlspecialchars(
$errorId)
    );
}

// Get unique states for filter
try {
    $states = $pdo->query("SELECT name FROM workflow_states ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $errorId = uniqid('STATES_');
    error_log(sprintf(
        "[%s] States query failed - Error: %s\nTrace:\n%s",
        $errorId,
        $e->getMessage(),
        $e->getTraceAsString()
    ));
    $states = [];
    // Don't show error to user since this doesn't break core functionality
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workflow Monitor</title>
    <style>
        .filter-box { padding: 20px; background: #f5f5f5; margin-bottom: 20px; }
        .transition-list { margin-top: 20px; }
        .transition-item { 
            padding: 10px; 
            border-left: 4px solid;
            margin-bottom: 10px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .progress-visual {
            padding: 20px;
            background: #fff;
            margin: 20px 0;
        }
        .state-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h1>Workflow Transitions</h1>
    <?php if (!empty($error)): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) 
?>        </div>
    <?php endif; ?>
    <div class="filter-box">
        <form method="get">
            <label>From: <input type="date" name="start_date"></label>
            <label>To: <input type="date" name="end_date"></label>
            <label>State:
                <select name="state">
                    <option value="">All States</option>
                    <?php foreach ($states as $state): ?>                        <option value="<?= htmlspecialchars($state) ?>" 
                            <?= ($_GET['state'] ?? '') === $state ? 'selected' : '' ?>>
                            <?= htmlspecialchars($state) 
?>                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="progress-visual">
        <h3>Recent State Distribution</h3>
        <?php
        $stateCounts = [];
        foreach ($transitions as $t) {
            $state = $t['to_state'];
            $stateCounts[$state] = ($stateCounts[$state] ?? 0) + 1;
        }
        arsort($stateCounts);
        ?>
        <?php foreach ($stateCounts as $state => $count): ?>
            <div style="margin: 5px 0; padding: 5px; background: #e9ecef; width: <?= min($count * 10, 100) ?>%">
                <?= htmlspecialchars($state) ?> (<?= $count ?>)
            </div>
        <?php endforeach; ?>
    </div>

    <div class="transition-list">
        <?php foreach ($transitions as $transition): ?>
            <div class="transition-item">
                <div style="color: #666; font-size: 0.9em;">
                    <?= htmlspecialchars($transition['transitioned_at']) 
?>                </div>
                <div style="margin: 5px 0;">
                    <strong><?= htmlspecialchars($transition['content_title']) ?></strong>
                    <?php if ($transition['from_state']): ?>                        → from <span class="state-badge" style="background: #ffeeba">
                            <?= htmlspecialchars($transition['from_state']) 
?>                        </span>
                    <?php endif; ?>                    to <span class="state-badge" style="background: #c3e6cb">
                        <?= htmlspecialchars($transition['to_state']) 
?>                    </span>
                    by <?= htmlspecialchars($transition['user_name'] ?? 'System') 
?>                </div>
                <?php if ($transition['notes']): ?>
                    <div style="color: #666; margin-top: 5px;">
                        <?= nl2br(htmlspecialchars($transition['notes'])) 
?>                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
