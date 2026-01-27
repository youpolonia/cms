<?php
/**
 * Plugin Installation View
 */
?><div class="container">
    <h1>Install New Plugin</h1>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error);  ?>
    </div>
    <?php endif;  ?>
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="plugin_id" class="form-label">Plugin</label>
                    <select class="form-select" id="plugin_id" name="plugin_id" required>
                        <option value="">-- Select Plugin --</option>
                        <?php foreach ($plugins as $plugin): ?>
                        <option value="<?php echo $plugin['id']; ?>">
                            <?php echo htmlspecialchars($plugin['name']); ?> (v<?php echo $plugin['version']; ?>)
                        </option>
                        <?php endforeach;  ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="license_key" class="form-label">License Key (if required)</label>
                    <input type="text" class="form-control" id="license_key" name="license_key">
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="/admin/plugins" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Install Plugin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>