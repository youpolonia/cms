<?php require_once __DIR__ . '/../includes/header.php'; 
?><div class="container">
    <h1>System Health Dashboard</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">System Information</div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>PHP Version</th>
                            <td><?= $healthData['system']['php_version'] ?></td>
                        </tr>
                        <tr>
                            <th>Server Software</th>
                            <td><?= $healthData['system']['server_software'] ?></td>
                        </tr>
                        <tr>
                            <th>CMS Version</th>
                            <td><?= $healthData['system']['cms_version'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">Database Status</div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Status</th>
                            <td><?= $healthData['database']['status'] ?></td>
                        </tr>
                        <tr>
                            <th>Version</th>
                            <td><?= $healthData['database']['version'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Resource Usage</div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>Disk Space</h5>
                        <div class="progress">
                            <?php 
                            $usedPercent = 100 - ($healthData['resources']['disk_space']['free'] / $healthData['resources']['disk_space']['total'] * 100);
?>                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?= $usedPercent ?>%" 
                                 aria-valuenow="<?= $usedPercent ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= round($usedPercent) ?>%
                            </div>
                        </div>
                        <small>
                            <?= $this->fileUtils->formatBytes($healthData['resources']['disk_space']['free']) ?> free of 
                            <?= $this->fileUtils->formatBytes($healthData['resources']['disk_space']['total']) 
?>                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Memory Usage</h5>
                        <div class="progress">
                            <?php 
                            $memoryPercent = ($healthData['resources']['memory']['usage'] / $healthData['resources']['memory']['peak'] * 100);
?>                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?= $memoryPercent ?>%" 
                                 aria-valuenow="<?= $memoryPercent ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= round($memoryPercent) ?>%
                            </div>
                        </div>
                        <small>
                            <?= $this->fileUtils->formatBytes($healthData['resources']['memory']['usage']) ?> used (Peak: 
                            <?= $this->fileUtils->formatBytes($healthData['resources']['memory']['peak']) ?>)
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Writable Directories</div>
                <div class="card-body">
                    <table class="table">
                        <?php foreach ($healthData['checks']['writable_dirs'] as $name => $writable): ?>
                            <tr>
                                <th><?= ucfirst($name) ?></th>
                                <td>
                                    <span class="badge bg-<?= $writable ? 'success' : 'danger' ?>">
                                        <?= $writable ? 'Writable' : 'Not Writable' 
?>                                    </span>
                                </td>
                            </tr>
                        <?php endforeach;  ?>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Required Extensions</div>
                <div class="card-body">
                    <table class="table">
                        <?php foreach ($healthData['checks']['required_extensions'] as $ext => $loaded): ?>
                            <tr>
                                <th><?= $ext ?></th>
                                <td>
                                    <span class="badge bg-<?= $loaded ? 'success' : 'danger' ?>">
                                        <?= $loaded ? 'Loaded' : 'Missing' 
?>                                    </span>
                                </td>
                            </tr>
                        <?php endforeach;  ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php';
