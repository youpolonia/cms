<?php
/**
 * 403 Forbidden template
 */
$this->extend('base.php');

$this->block('content', function($vars) {

?><div class="error-page">
        <h1>Access Denied</h1>
        <p>You don't have permission to view this content.</p>
        <p><a href="/">Return to homepage</a></p>
    </div>
<?php });
