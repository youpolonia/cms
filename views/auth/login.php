/**
 * Login View
 * Includes tenant selection for multi-tenancy
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form method="POST" action="/auth/login">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email"
 required>
?>        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password"
 required>
?>        </div>
        <div>
            <label for="tenant_id">Tenant:</label>
            <select id="tenant_id" name="tenant_id"
 required>
                <?php foreach ($tenants as $tenant): ?>                    <option value="<?= htmlspecialchars($tenant['id']) ?>">
                        <?= htmlspecialchars($tenant['name'])  ?>
                    </option>
                <?php endforeach;  ?>
            </select>
        </div>
        <button type="submit">Login</button>
    </form>
</body>
</html>
