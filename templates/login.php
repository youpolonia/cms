<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background-color: #0069d9;
        }
        .error {
            color: #dc3545;
            margin-bottom: 1rem;
            text-align: center;
        }
        .csrf-token {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>CMS Login</h1>
        
        <?php if (!empty($error)): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif;  ?>
        
<form method="POST" action="/login">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
 required>
?>            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
 required>
?>            </div>
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
