<?php
/**
 * 500 Internal Server Error — Frontend error page
 */
http_response_code(500);
// Don't use DB or complex includes here — they might be the cause of the 500
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            text-align: center;
            max-width: 500px;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #e2e8f0;
            line-height: 1;
        }
        h2 { color: #1e293b; margin: 16px 0 8px; font-size: 1.5rem; }
        p { color: #64748b; margin-bottom: 24px; line-height: 1.6; }
        .btn {
            display: inline-block;
            padding: 10px 24px;
            background: #6366f1;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
        }
        .btn:hover { background: #4f46e5; }
        .details {
            margin-top: 32px;
            padding: 16px;
            background: #f1f5f9;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">500</div>
        <h2>Something went wrong</h2>
        <p>We're experiencing a temporary issue. Please try again in a few moments. If the problem persists, contact the site administrator.</p>
        <a href="/" class="btn">← Go Home</a>
        <?php if (defined('CMS_DEBUG') && CMS_DEBUG && !empty($error)): ?>
            <div class="details">
                <strong>Debug info:</strong><br>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
