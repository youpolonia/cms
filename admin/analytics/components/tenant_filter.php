<?php
if (empty($tenants)) {
    echo '<div class="no-tenants">No tenant data available</div>';
    return;
}

$currentTenant = $_SESSION['analytics_tenant'] ?? '';

?><div class="tenant-filter">
    <form id="tenantForm" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
        <?= csrf_field(); ?>        <label for="tenantSelect">Filter by Tenant:</label>
        <select name="tenant" id="tenantSelect" class="tenant-select">
            <option value="" <?= empty($currentTenant) ? 'selected' : '' ?>>All Tenants</option>
            <?php foreach ($tenants as $tenant): ?>                <option value="<?= htmlspecialchars($tenant['id']) ?>" 
                    <?= $currentTenant == $tenant['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tenant['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="filter_action" value="apply_tenant">
    </form>
</div>

<script>
document.getElementById('tenantSelect').addEventListener('change', function() {
    document.getElementById('tenantForm').submit();
});
</script>

<style>
.tenant-filter {
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
}

.tenant-filter label {
    margin-right: 0.5rem;
    font-weight: 600;
}

.tenant-select {
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    min-width: 200px;
}

.no-tenants {
    padding: 1rem;
    background: #f8d7da;
    color: #721c24;
    border-radius: 4px;
    margin-bottom: 2rem;
}
</style>
