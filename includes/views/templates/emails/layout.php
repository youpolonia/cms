<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title ?? 'Email Notification'; ?></title>
    <style>
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .email-header {
            padding: 20px;
            background-color: #f8f9fa;
            text-align: center;
        }
        .email-content {
            padding: 20px;
        }
        .email-footer {
            padding: 20px;
            background-color: #f8f9fa;
            text-align: center;
            font-size: 0.9em;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <?php View::yield('header'); 
?>        </div>
        
        <div class="email-content">
            <?php View::yield('content'); 
?>        </div>
        
        <div class="email-footer">
            <?php View::yield('footer'); 
?>        </div>
    </div>
</body>
</html>
