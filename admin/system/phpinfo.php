<?php
// Verify admin access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Output phpinfo with minimal styling
echo '
<!DOCTYPE html>
<html>
<head>
    <title>PHP Info</title>
    <style>
        body { 
            background: white; 
            margin: 0; 
            padding: 10px;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        .phpinfo { 
            margin: 0 auto; 
            width: 95%; 
        }
        .phpinfo pre { 
            margin: 0; 
            font-family: monospace; 
        }
    </style>
</head>
<body>
    <div class="phpinfo">
        <?php phpinfo(); ?>
    </div>
</body>
</html>';
