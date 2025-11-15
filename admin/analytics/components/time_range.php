<?php
$currentRange = $_SESSION['analytics_range'] ?? 'week';
$customStart = $_SESSION['analytics_custom_start'] ?? '';
$customEnd = $_SESSION['analytics_custom_end'] ?? date('Y-m-d');

?><div class="time-range">
    <form id="rangeForm" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
        <?= csrf_field(); ?>        <label for="rangeSelect">Time Range:</label>
        <select name="range" id="rangeSelect" class="range-select">
            <option value="today" <?= $currentRange === 'today' ? 'selected' : '' ?>>Today</option>
            <option value="week" <?= $currentRange === 'week' ? 'selected' : '' ?>>This Week</option>
            <option value="month" <?= $currentRange === 'month' ? 'selected' : '' ?>>This Month</option>
            <option value="custom" <?= $currentRange === 'custom' ? 'selected' : '' ?>>Custom Range</option>
        </select>

        <div id="customRangeFields" class="custom-range-fields" style="display: <?= $currentRange === 'custom' ? 'block' : 'none' ?>">
            <label for="customStart">From:</label>
            <input type="date" name="custom_start" id="customStart" value="<?= $customStart ?>" max="<?= date('Y-m-d') ?>">
            
            <label for="customEnd">To:</label>
            <input type="date" name="custom_end" id="customEnd" value="<?= $customEnd ?>" max="<?= date('Y-m-d') ?>">
        </div>

        <input type="hidden" name="filter_action" value="apply_range">
    </form>
</div>

<script>
document.getElementById('rangeSelect').addEventListener('change', function() {
    const customFields = document.getElementById('customRangeFields');
    customFields.style.display = this.value === 'custom' ? 'block' : 'none';
    document.getElementById('rangeForm').submit();
});

document.querySelectorAll('#customRangeFields input').forEach(input => {
    input.addEventListener('change', function() {
        document.getElementById('rangeForm').submit();
    });
});
</script>

<style>
.time-range {
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
}

.time-range label {
    margin-right: 0.5rem;
    font-weight: 600;
}

.range-select {
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    min-width: 150px;
}

.custom-range-fields {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

.custom-range-fields label {
    display: inline-block;
    min-width: 50px;
    margin-right: 0.5rem;
}

.custom-range-fields input[type="date"] {
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    margin-right: 1rem;
}
</style>
