<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/database.php';
function authenticateAdmin(string $usernameOrEmail, string $password, string $table = 'admins'): array
{
    try {
        $pdo = \core\Database::connection();
        $allowed = ['admins','users'];
        if (!in_array($table, $allowed, true)) { return [false, null]; }
        $safe = "`{$table}`";
        $desc = $pdo->query("DESCRIBE {$safe}")->fetchAll(\PDO::FETCH_ASSOC);
        if (!$desc) { return [false, null]; }
        $cols = array_column($desc, 'Field');
        $userCol = in_array('username', $cols, true) ? 'username' : (in_array('email', $cols, true) ? 'email' : null);
        if ($userCol === null) { return [false, null]; }
        $passCol = null;
        foreach (['password_hash','password','pass','hash'] as $pc) { if (in_array($pc, $cols, true)) { $passCol = $pc; break; } }
        if ($passCol === null) { return [false, null]; }
        $u = trim($usernameOrEmail);
        $stmt = $pdo->prepare("SELECT * FROM {$safe} WHERE `{$userCol}` = :u LIMIT 1");
        $stmt->execute([':u' => $u]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) { return [false, null]; }
        if (!password_verify($password, $row[$passCol])) { return [false, null]; }
        unset($row[$passCol]);
        return [true, $row];
    } catch (\Throwable $e) {
        error_log('[authenticateAdmin] ' . $e->getMessage());
        return [false, null];
    }
}
