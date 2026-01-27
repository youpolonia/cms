<?php
/**
 * Plugin Details View
 */
?><div class="container">
    <h1><?php echo htmlspecialchars($plugin['name']); ?></h1>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    Plugin Information
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Version</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($plugin['version']); ?></dd>

                        <dt class="col-sm-4">Author</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($plugin['author']); ?></dd>

                        <dt class="col-sm-4">License</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($plugin['license_type']); ?></dd>

                        <dt class="col-sm-4">Installed</dt>
                        <dd class="col-sm-8"><?php echo date('Y-m-d', strtotime($plugin['installed_at'])); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Plugin Settings
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/plugins/<?php echo $plugin['id']; ?>/settings">
                        <?php foreach ($plugin['settings'] as $key => $value): ?>
                        <div class="mb-3">
                            <label for="<?php echo $key; ?>" class="form-label">
                                <?php echo ucwords(str_replace('_', ' ', $key));  ?>
                            </label>
                            <input type="text" class="form-control" id="<?php echo $key; ?>" 
                                   name="settings[<?php echo $key; ?>]" 
                                   value="<?php echo htmlspecialchars($value); ?>">
                        </div>
                        <?php endforeach;  ?>
                        <button type="submit" class="btn btn-primary">
                            Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="/admin/plugins" class="btn btn-secondary">
            Back to Plugins
        </a>
        <a href="/admin/plugins/<?php echo $plugin['id']; ?>/uninstall" 
           class="btn btn-danger float-end"
           onclick="return confirm('Are you sure?')">
            Uninstall Plugin
        </a>
    </div>
</div>