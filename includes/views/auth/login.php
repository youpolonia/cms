<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Login' ?></title>
    <style>
        .login-wrap{max-width:420px;margin:6rem auto;padding:2rem;border:1px solid #e5e7eb;border-radius:8px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;}
        .login-wrap h1{margin:0 0 1rem 0;font-size:1.5rem}
        .field{margin-bottom:1rem}
        .field label{display:block;margin-bottom:.5rem;font-weight:600}
        .field input{width:100%;padding:.6rem .75rem;border:1px solid #d1d5db;border-radius:6px}
        .btn{display:inline-block;background:#111827;color:#fff;padding:.6rem 1rem;border:none;border-radius:6px;cursor:pointer}
        .error{background:#fee2e2;border:1px solid #ef4444;color:#991b1b;padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem}
    </style>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'">
</head>
<body>
<main class="login-wrap">
    <h1><?= isset($title) ? htmlspecialchars($title) : 'Login' ?></h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <?php
        if (function_exists('csrf_field')) {
            echo csrf_field();
        }
        ?>
        <div class="field">
            <label for="login-username">Username or Email</label>
            <input id="login-username" name="username" type="text" autocomplete="username" required>
        </div>
        <div class="field">
            <label for="login-password">Password</label>
            <input id="login-password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="btn" type="submit">Sign in</button>
    </form>
</main>
</body>
</html>
