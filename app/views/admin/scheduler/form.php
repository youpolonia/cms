<?php
$title = $isEdit ? 'Edit Job' : 'Create Job';
ob_start();
?>

<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h2 class="card-title"><?= $isEdit ? 'Edit Job' : 'Create New Job' ?></h2>
        <a href="/admin/scheduler" class="btn btn-ghost btn-sm">&larr; Back to Scheduler</a>
    </div>
    
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/admin/scheduler/' . $job['id'] : '/admin/scheduler' ?>">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label class="form-label" for="name"<span class="tip"><span class="tip-text">A descriptive name for this scheduled task.</span></span>>Job Name <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="name" name="name" class="form-input" required 
                       value="<?= esc($job['name'] ?? '') ?>" placeholder="e.g. Daily Backup">
                <small class="form-hint">A descriptive name for this scheduled job</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="job_type">Job Type</label>
                <select id="job_type" name="job_type" class="form-select">
                    <option value="cron" <?= ($job['job_type'] ?? 'cron') === 'cron' ? 'selected' : '' ?>>Cron Job</option>
                    <option value="webhook" <?= ($job['job_type'] ?? '') === 'webhook' ? 'selected' : '' ?>>Webhook</option>
                    <option value="email" <?= ($job['job_type'] ?? '') === 'email' ? 'selected' : '' ?>>Email Task</option>
                    <option value="cleanup" <?= ($job['job_type'] ?? '') === 'cleanup' ? 'selected' : '' ?>>Cleanup Task</option>
                    <option value="sync" <?= ($job['job_type'] ?? '') === 'sync' ? 'selected' : '' ?>>Data Sync</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="schedule_expression">Schedule Expression <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="schedule_expression" name="schedule_expression" class="form-input" required 
                       value="<?= esc($job['schedule_expression'] ?? '') ?>" placeholder="*/15 * * * *">
                <small class="form-hint">Cron format: <code>minute hour day month weekday</code></small>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem;">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('schedule_expression').value='* * * * *'">Every min</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('schedule_expression').value='*/15 * * * *'">Every 15m</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('schedule_expression').value='0 * * * *'">Hourly</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('schedule_expression').value='0 0 * * *'">Daily</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('schedule_expression').value='0 0 * * 0'">Weekly</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('schedule_expression').value='0 0 1 * *'">Monthly</button>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="active" <?= ($job['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="disabled" <?= ($job['status'] ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
            
            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border);">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Job' : 'Create Job' ?></button>
                <a href="/admin/scheduler" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
