<div class="workflow-state-selector">
    <h4>Current State: <span class="state-badge state-<?= htmlspecialchars($current_state) ?>">
        <?= htmlspecialchars(ucfirst($current_state))  ?>
    </span></h4>

    <?php if (!empty($available_states)): ?>
        <div class="state-actions">
            <form method="post" action="/admin/workflow/change-state">
                <input type="hidden" name="content_id" value="<?= $content_id ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="btn-group">
                    <?php foreach ($available_states as $state): ?>
                        <button type="submit" 
                                name="new_state" 
                                value="<?= $state ?>"
                                class="btn btn-state btn-state-<?= $state ?>">
                            <?= ucfirst($state)  ?>
                        </button>
                    <?php endforeach;  ?>
                </div>
            </form>
        </div>
    <?php else: ?>
        <p>No available state transitions</p>
    <?php endif;  ?>
</div>

<style>
.state-badge {
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: bold;
}
.state-draft { background: #ffc107; color: #000; }
.state-review { background: #17a2b8; color: #fff; }
.state-published { background: #28a745; color: #fff; }
.state-archived { background: #6c757d; color: #fff; }

.btn-state {
    padding: 5px 15px;
    margin-right: 5px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-state-draft { background: #ffc107; }
.btn-state-review { background: #17a2b8; color: white; }
.btn-state-published { background: #28a745; color: white; }
.btn-state-archived { background: #6c757d; color: white; }
</style>
