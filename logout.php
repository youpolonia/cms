<?php
require_once __DIR__ . '/includes/auth/Auth.php';

$auth = new \Includes\Auth\Auth();
$auth->logout();

// Redirect to login page
header('Location: /login.php');
exit;
