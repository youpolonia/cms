<?php
/**
 * Run All Tests — Pure PHP, Shared Hosting Compatible
 * No exec(), no shell, no CLI required.
 * 
 * @package JessieCMS
 * @since 2026-02-08
 */

// Security: require admin login for web access
if (php_sapi_name() !== 'cli') {
    require_once dirname(__DIR__) . '/config.php';
    require_once CMS_ROOT . '/core/bootstrap.php';
    if (empty($_SESSION['admin_id'])) {
        http_response_code(403);
        die('Access denied');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

// Suppress session warnings when tests include bootstrap multiple times
$originalErrorReporting = error_reporting();
error_reporting($originalErrorReporting & ~E_WARNING);

echo "╔════════════════════════════════════╗\n";
echo "║   JESSIE CMS TEST SUITE            ║\n";
echo "╚════════════════════════════════════╝\n\n";

$testDir = __DIR__;
$tests = [
    'DatabaseTest.php',
    'RouterTest.php',
    'CsrfTest.php',
    'JtbElementTest.php',
    'JtbTemplateTest.php',
    'CacheTest.php',
    'HelpersTest.php',
    'AuthTest.php',
    'SeoTest.php',
    'JtbCssExtractorTest.php',
    'AiConfigTest.php',
    'EventBusTest.php',
    'MvcControllersTest.php',
    'ContentRendererTest.php',
];

$totalPassed = 0;
$totalFailed = 0;

foreach ($tests as $testFile) {
    echo "━━━ Running $testFile ━━━\n";

    ob_start();
    try {
        include $testDir . '/' . $testFile;
    } catch (\Throwable $e) {
        echo "💥 CRASH: " . $e->getMessage() . "\n";
    }
    $output = ob_get_clean();
    echo $output;

    if (preg_match('/Passed:\s*(\d+)/', $output, $m)) {
        $totalPassed += (int) $m[1];
    }
    if (preg_match('/Failed:\s*(\d+)/', $output, $m)) {
        $totalFailed += (int) $m[1];
    }

    echo "\n";
}

echo "╔════════════════════════════════════╗\n";
echo "║   FINAL RESULTS                    ║\n";
echo "╠════════════════════════════════════╣\n";
printf("║   Total Passed: %-18d║\n", $totalPassed);
printf("║   Total Failed: %-18d║\n", $totalFailed);
printf("║   Total Tests:  %-18d║\n", $totalPassed + $totalFailed);
echo "╚════════════════════════════════════╝\n";

if ($totalFailed === 0) {
    echo "\n✅ ALL TESTS PASSED!\n";
} else {
    echo "\n❌ SOME TESTS FAILED\n";
}
