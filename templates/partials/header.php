<?php
/**
 * Header Partial
 */
?><header class="site-header">
    <div class="header-container">
        <div class="branding">
            <a href="/" class="logo">CMS</a>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="/admin">Dashboard</a></li>
                <li><a href="/content">Content</a></li>
                <li><a href="/settings">Settings</a></li>
            </ul>
        </nav>
        <div class="user-controls">
            <?php if (Auth::check()): ?>
                <span>Welcome, <?= htmlspecialchars(Auth::user()->name) ?></span>
                <a href="/logout">Logout</a>
            <?php else: ?>
                <a href="/login">Login</a>
            <?php endif;  ?>
        </div>
    </div>
</header>
