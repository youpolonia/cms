<?php
/**
 * Admin Dashboard Template
 */
require_once __DIR__ . '/../layout.php';

$sessionService = new \Auth\Services\SessionService();
$sessionService->start();

$timeout = 1800; // 30 minutes in seconds
$remainingTime = $timeout - (time() - $sessionService->getLastActivity());
$minutes = floor($remainingTime / 60);
$seconds = $remainingTime % 60;
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/../partials/head.php'; 
?>    <title>Admin Dashboard</title>
    <style>
        .session-timeout {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .session-warning {
            background: #fff3cd;
            border-color: #ffeeba;
        }
        .session-critical {
            background: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../partials/header.php'; 
?>    <div class="container-fluid">
        <div class="row">
            <?php require_once __DIR__ . '/partials/sidebar.php'; 
?>            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/auth/logout" class="btn btn-danger">Logout</a>
                    </div>
                </div>
                
                <!-- Dashboard content here -->
                <div class="alert alert-info">
                    Welcome to the Admin Dashboard
                </div>
            </main>
        </div>
    </div>

    <div class="session-timeout <?= $minutes < 5 ? 'session-warning' : '' ?> <?= $minutes < 2 ? 'session-critical' : '' ?>">
        Session expires in: <?= $minutes ?>m <?= $seconds ?>s
    </div>

    <script>
        // Update session timer every second
        setInterval(() => {
            const timeoutElement = document.querySelector('.session-timeout');
            const text = timeoutElement.textContent;
            const matches = text.match(/Session expires in: (\d+)m (\d+)s/);
            
            if (matches) {
                let minutes = parseInt(matches[1]);
                let seconds = parseInt(matches[2]);
                
                seconds--;
                if (seconds < 0) {
                    seconds = 59;
                    minutes--;
                }
                
                if (minutes < 0) {
                    window.location.href = '/auth/logout';
                    return;
                }
                
                timeoutElement.textContent = `Session expires in: ${minutes}m ${seconds}s`;
                
                // Update warning classes
                timeoutElement.classList.remove('session-warning', 'session-critical');
                if (minutes < 5) {
                    timeoutElement.classList.add('session-warning');
                }
                if (minutes < 2) {
                    timeoutElement.classList.add('session-critical');
                }
            }
        }, 1000);
?>    </script>
</body>
</html>
