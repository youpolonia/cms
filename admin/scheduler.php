<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
$result = null;

// Storage path
$SCH_DIR  = __DIR__ . '/../cms_storage/scheduler';
$SCH_FILE = $SCH_DIR . '/jobs.json';
if (!is_dir($SCH_DIR)) { @mkdir($SCH_DIR, 0775, true); }

// Load jobs
$jobs = [];
if (is_file($SCH_FILE)) {
    $json = @file_get_contents($SCH_FILE);
    if ($json !== false) {
        $arr = json_decode($json, true);
        if (is_array($arr)) { $jobs = $arr; }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = (string)($_POST['action'] ?? 'add');
    if ($action === 'delete') {
        $id = (string)($_POST['id'] ?? '');
        $before = count($jobs);
        $jobs = array_values(array_filter($jobs, function($j) use ($id){ return isset($j['id']) && $j['id'] !== $id; }));
        $after = count($jobs);
        $ok = @file_put_contents($SCH_FILE, json_encode($jobs, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), LOCK_EX);
        $result = ($ok === false)
            ? ['type'=>'error','msg'=>'Failed to delete job']
            : ['type'=>'success','msg'=>'Job deleted','deleted'=>($before-$after)];
    } else {
        $job = mb_substr(trim((string)($_POST['job'] ?? '')), 0, 120);
        $cron = trim((string)($_POST['cron'] ?? ''));
        if ($job === '' || $cron === '') {
            $result = ['type'=>'error','msg'=>'Job and cron are required'];
        } elseif (!preg_match('/^([\*\d\/,-]+\s+){4}[\*\d\/,-]+$/', $cron)) {
            $result = ['type'=>'error','msg'=>'Invalid cron expression'];
        } else {
            $id = bin2hex(random_bytes(6));
            $jobs[] = ['id'=>$id,'job'=>$job,'cron'=>$cron,'created_at'=>gmdate('c')];
            $ok = @file_put_contents($SCH_FILE, json_encode($jobs, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), LOCK_EX);
            $result = ($ok === false)
                ? ['type'=>'error','msg'=>'Failed to save job']
                : ['type'=>'success','msg'=>'Job scheduled','id'=>$id];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Scheduler</title><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body>
<main class="container">
    <h1>Scheduler</h1>
    <?php if ($result): ?>
        <div class="notice <?=htmlspecialchars($result['type'])?>"><strong><?=htmlspecialchars($result['msg'])?></strong></div>
        <?php if (!empty($result['id'])): ?><p><b>ID:</b> <?=htmlspecialchars($result['id'])?></p><?php endif; ?>
    <?php endif; ?>
    <section>
        <h2>Add Job</h2>
        <form method="post">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="add">
            <div><label for="job">Job name</label><input id="job" name="job" type="text" required></div>
            <div><label for="cron">Cron expression</label><input id="cron" name="cron" type="text" placeholder="*/15 * * * *" required></div>
            <button type="submit">Schedule</button>
        </form>
    </section>
    <section>
        <h2>Planned Jobs</h2>
        <?php if (!$jobs): ?>
            <p>No jobs yet.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>ID</th><th>Job</th><th>Cron</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($jobs as $j): ?>
                    <tr>
                        <td><?=htmlspecialchars($j['id'])?></td>
                        <td><?=htmlspecialchars($j['job'])?></td>
                        <td><?=htmlspecialchars($j['cron'])?></td>
                        <td><?=htmlspecialchars($j['created_at'])?></td>
                        <td>
                            <form method="post" style="display:inline">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?=htmlspecialchars($j['id'])?>">
                                <button type="submit" onclick="return confirm('Delete this job?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
<?php require_once __DIR__ . '/includes/footer.php';
