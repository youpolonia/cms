/**
 * Admin Dashboard View
 */

?><!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <div class="stats">
        <p>Users: <?= $data['stats']['users'] ?? 0 ?></p>
        <p>Content: <?= $data['stats']['content'] ?? 0 ?></p>
    </div>
</body>
</html>
