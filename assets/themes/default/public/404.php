<?php
/**
 * 404 Error template
 */
$this->extend('base.php');

$this->block('content', function($vars) {

?><div class="error-page">
        <h1>Page Not Found</h1>
        <p>The requested page could not be found.</p>
        <p><a href="/">Return to homepage</a></p>
    </div>
<?php });
