<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

echo "<h1>Session Debug</h1>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>admin_id: " . ($_SESSION['admin_id'] ?? 'NOT SET') . "</p>";
echo "<p>admin_role: " . ($_SESSION['admin_role'] ?? 'NOT SET') . "</p>";
echo "<p>admin_username: " . ($_SESSION['admin_username'] ?? 'NOT SET') . "</p>";
echo "<hr>";
echo "<p>If admin_id is NOT SET, <a href='/admin/login.php'>login first</a></p>";
echo "<p>If admin_id IS SET, the session works and problem is elsewhere</p>";
