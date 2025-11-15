/**
 * Assets Partial
 */

<!-- Core CSS -->
?><link rel="stylesheet" href="/assets/css/styles.css">
<link rel="stylesheet" href="/assets/css/auth.css">

<!-- Core JS -->
<script src="/assets/js/main.js" defer></script>
<script src="/assets/js/recommendation-client.js" defer></script>

<!-- Admin-specific assets -->
<?php if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0): ?>
    <link rel="stylesheet" href="/assets/admin/css/admin.css">
    <script src="/assets/admin/js/admin.js" defer></script>
<?php endif;
