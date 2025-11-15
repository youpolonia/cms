<?php
/**
 * Admin Navigation Template
 * 
 * Main navigation for admin interface
 * 
 * @package CMS
 * @subpackage Admin
 * @version 1.0.0
 */

?><nav class="admin-nav">
    <ul>
        <li><a href="?section=dashboard" class="<?= ($_GET['section'] ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="?section=content" class="<?= ($_GET['section'] ?? '') === 'content' ? 'active' : '' ?>">Content</a></li>
        <li><a href="?section=versions" class="<?= ($_GET['section'] ?? '') === 'versions' ? 'active' : '' ?>">Versions</a></li>
        <li><a href="?section=status" class="<?= ($_GET['section'] ?? '') === 'status' ? 'active' : '' ?>">System Status</a></li>
    </ul>
</nav>
