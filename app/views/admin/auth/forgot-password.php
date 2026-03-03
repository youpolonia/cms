<?php
/**
 * Forgot Password Page
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Jessie AI-CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
        }
        .header { text-align: center; margin-bottom: 2rem; }
        .header-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .header h1 { font-size: 1.5rem; font-weight: 600; color: #1e293b; }
        .header p { color: #64748b; font-size: 0.875rem; margin-top: 0.5rem; }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block; margin-bottom: 0.5rem;
            font-weight: 500; font-size: 0.875rem; color: #374151;
        }
        .form-group input {
            width: 100%; padding: 0.75rem 1rem; font-size: 1rem;
            border: 1px solid #d1d5db; border-radius: 8px; transition: all 0.2s;
        }
        .form-group input:focus {
            outline: none; border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        .btn {
            width: 100%; padding: 0.875rem; background: #6366f1; color: #fff;
            border: none; border-radius: 8px; font-size: 1rem;
            font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn:hover { background: #4f46e5; }
        .back-link {
            display: block; text-align: center; margin-top: 1.5rem;
            color: #6366f1; font-size: 0.875rem; text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="header-icon">🔑</div>
            <h1>Forgot Password</h1>
            <p>Enter your email address and we'll send you a reset link.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/forgot-password">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autofocus placeholder="admin@example.com">
            </div>
            <button type="submit" class="btn">Send Reset Link</button>
        </form>

        <a href="/admin/login" class="back-link">← Back to Login</a>
    </div>
</body>
</html>
