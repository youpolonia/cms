<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__.'/config.php';
require_once __DIR__.'/middlewares/authmiddleware.php';
require_once __DIR__.'/middlewares/inputvalidation.php';
require_once __DIR__.'/middlewares/ratelimiting.php';

// Initialize Redis connection
$redis = new Redis();
$redis->connect($config['redis']['host'], $config['redis']['port']);

// Initialize middlewares
$middlewares = [
    new RateLimiting(
        $redis,
        $config['rate_limiting']['limit'],
        $config['rate_limiting']['window']
    ),
    new AuthMiddleware(\core\Database::connection()),
    new InputValidation()
];

// Register middleware stack
$app = new ApiGateway();
foreach ($middlewares as $middleware) {
    $app->addMiddleware($middleware);
}

return $app;
