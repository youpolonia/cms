<?php
// Bootstrap
require_once __DIR__ . '/../../config.php';

// DEV_MODE gate
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Session
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

// RBAC
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();

// CSRF
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot();

// Database
require_once __DIR__ . '/../../core/database.php';

// Header and navigation
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';

// Check if settings table exists
$db = \core\Database::connection();
$tableExists = false;

try {
    $stmt = $db->query("SELECT 1 FROM settings LIMIT 1");
    $tableExists = true;
} catch (PDOException $e) {
    // Table likely doesn't exist
    if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "Table") !== false) {
        $tableExists = false;
    } else {
        // Other DB error - log it
        error_log("Settings table check error: " . $e->getMessage());
        $tableExists = false;
    }
}

// Graceful degradation if table doesn't exist
if (!$tableExists) {
    ?>
    <main class="container">
        <h1>System Settings</h1>
        <div class="alert alert-warning">
            <strong>Warning:</strong> The settings table does not exist in the database.
            <p>Please create the table using the following SQL statement:</p>
            <pre><?php echo htmlspecialchars("CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    group_name VARCHAR(50) NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", ENT_QUOTES, 'UTF-8'); ?></pre>
        </div>
    </main>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Initialize variables
$errors = [];
$successMessage = '';
$settings = [];

// Define manageable keys
$managedKeys = [
    'site_name',
    'site_tagline',
    'default_locale',
    'timezone',
    'date_format',
    'time_format',
    'items_per_page'
];

// POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    csrf_validate_or_403();

    // Rate limiting
    require_once __DIR__ . '/../../services/ai/airatelimiter.php';
    if (!ai_rate_limit_allow('admin_settings', 10, 300)) {
        $errors[] = 'Rate limit exceeded. Please wait a few minutes before saving settings again.';
    }

    // Read POST values
    $siteName = isset($_POST['site_name']) ? trim($_POST['site_name']) : '';
    $siteTagline = isset($_POST['site_tagline']) ? trim($_POST['site_tagline']) : '';
    $defaultLocale = isset($_POST['default_locale']) ? trim($_POST['default_locale']) : '';
    $timezone = isset($_POST['timezone']) ? trim($_POST['timezone']) : '';
    $dateFormat = isset($_POST['date_format']) ? trim($_POST['date_format']) : '';
    $timeFormat = isset($_POST['time_format']) ? trim($_POST['time_format']) : '';
    $itemsPerPageRaw = isset($_POST['items_per_page']) ? trim($_POST['items_per_page']) : '';

    // Validation
    if (mb_strlen($siteName, 'UTF-8') < 1 || mb_strlen($siteName, 'UTF-8') > 200) {
        $errors[] = 'Site name is required and must be between 1 and 200 characters.';
    }

    if (mb_strlen($siteTagline, 'UTF-8') > 200) {
        $errors[] = 'Site tagline must not exceed 200 characters.';
    }

    if (mb_strlen($defaultLocale, 'UTF-8') < 1 || mb_strlen($defaultLocale, 'UTF-8') > 10) {
        $errors[] = 'Default locale is required and must be between 1 and 10 characters.';
    }

    if (mb_strlen($timezone, 'UTF-8') < 1 || mb_strlen($timezone, 'UTF-8') > 64) {
        $errors[] = 'Timezone is required and must be between 1 and 64 characters.';
    }

    if (mb_strlen($dateFormat, 'UTF-8') < 1 || mb_strlen($dateFormat, 'UTF-8') > 32) {
        $errors[] = 'Date format is required and must be between 1 and 32 characters.';
    }

    if (mb_strlen($timeFormat, 'UTF-8') < 1 || mb_strlen($timeFormat, 'UTF-8') > 32) {
        $errors[] = 'Time format is required and must be between 1 and 32 characters.';
    }

    $itemsPerPage = (int)$itemsPerPageRaw;
    if ($itemsPerPage < 5 || $itemsPerPage > 100) {
        $errors[] = 'Items per page must be between 5 and 100.';
    }

    // If no validation errors, perform upsert
    if (empty($errors)) {
        try {
            $updatedAt = gmdate('Y-m-d H:i:s');
            $groupName = 'general';

            $settingsToSave = [
                'site_name' => $siteName,
                'site_tagline' => $siteTagline,
                'default_locale' => $defaultLocale,
                'timezone' => $timezone,
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'items_per_page' => (string)$itemsPerPage
            ];

            foreach ($settingsToSave as $key => $value) {
                // Check if exists
                $stmt = $db->prepare("SELECT id FROM settings WHERE `key` = :key");
                $stmt->execute([':key' => $key]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    // Update
                    $stmt = $db->prepare("UPDATE settings SET `value` = :value, updated_at = :updated_at WHERE id = :id");
                    $stmt->execute([
                        ':value' => $value,
                        ':updated_at' => $updatedAt,
                        ':id' => $existing['id']
                    ]);
                } else {
                    // Insert
                    $stmt = $db->prepare("INSERT INTO settings (`key`, `value`, group_name, updated_at) VALUES (:key, :value, :group_name, :updated_at)");
                    $stmt->execute([
                        ':key' => $key,
                        ':value' => $value,
                        ':group_name' => $groupName,
                        ':updated_at' => $updatedAt
                    ]);
                }
            }

            $successMessage = 'Settings have been saved successfully.';
        } catch (PDOException $e) {
            error_log("Settings save error: " . $e->getMessage());
            $errors[] = 'An error occurred while saving settings. Please try again.';
        }
    }
}

// Load current settings from database
try {
    $placeholders = implode(',', array_fill(0, count($managedKeys), '?'));
    $stmt = $db->prepare("SELECT `key`, `value` FROM settings WHERE `key` IN ($placeholders)");
    $stmt->execute($managedKeys);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
} catch (PDOException $e) {
    error_log("Settings load error: " . $e->getMessage());
    // Continue with empty settings
}

// Determine display values (prefer POST if validation failed, then DB, then defaults)
$displayValues = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)) {
    // Use posted values when there are validation errors
    $displayValues['site_name'] = $_POST['site_name'] ?? '';
    $displayValues['site_tagline'] = $_POST['site_tagline'] ?? '';
    $displayValues['default_locale'] = $_POST['default_locale'] ?? '';
    $displayValues['timezone'] = $_POST['timezone'] ?? '';
    $displayValues['date_format'] = $_POST['date_format'] ?? '';
    $displayValues['time_format'] = $_POST['time_format'] ?? '';
    $displayValues['items_per_page'] = $_POST['items_per_page'] ?? '';
} else {
    // Use DB values or defaults
    $displayValues['site_name'] = $settings['site_name'] ?? 'My CMS';
    $displayValues['site_tagline'] = $settings['site_tagline'] ?? '';
    $displayValues['default_locale'] = $settings['default_locale'] ?? 'en_GB';
    $displayValues['timezone'] = $settings['timezone'] ?? 'Europe/London';
    $displayValues['date_format'] = $settings['date_format'] ?? 'Y-m-d';
    $displayValues['time_format'] = $settings['time_format'] ?? 'H:i';
    $displayValues['items_per_page'] = $settings['items_per_page'] ?? '20';
}
?>

<main class="container">
    <h1>System Settings</h1>

    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Validation errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/settings/">
        <?php csrf_field(); ?>

        <div class="form-group">
            <label for="site_name">Site Name <span class="required">*</span></label>
            <input
                type="text"
                id="site_name"
                name="site_name"
                value="<?php echo htmlspecialchars($displayValues['site_name'], ENT_QUOTES, 'UTF-8'); ?>"
                maxlength="200"
                required
            >
            <small class="muted">The name of your site (1-200 characters)</small>
        </div>

        <div class="form-group">
            <label for="site_tagline">Site Tagline</label>
            <input
                type="text"
                id="site_tagline"
                name="site_tagline"
                value="<?php echo htmlspecialchars($displayValues['site_tagline'], ENT_QUOTES, 'UTF-8'); ?>"
                maxlength="200"
            >
            <small class="muted">A brief description of your site (0-200 characters)</small>
        </div>

        <div class="form-group">
            <label for="default_locale">Default Locale <span class="required">*</span></label>
            <input
                type="text"
                id="default_locale"
                name="default_locale"
                value="<?php echo htmlspecialchars($displayValues['default_locale'], ENT_QUOTES, 'UTF-8'); ?>"
                maxlength="10"
                required
                placeholder="en_GB"
            >
            <small class="muted">Language and region code (e.g., en_GB, pl_PL)</small>
        </div>

        <div class="form-group">
            <label for="timezone">Timezone <span class="required">*</span></label>
            <input
                type="text"
                id="timezone"
                name="timezone"
                value="<?php echo htmlspecialchars($displayValues['timezone'], ENT_QUOTES, 'UTF-8'); ?>"
                maxlength="64"
                required
                placeholder="Europe/London"
            >
            <small class="muted">Timezone identifier (e.g., Europe/London, America/New_York)</small>
        </div>

        <div class="form-group">
            <label for="date_format">Date Format <span class="required">*</span></label>
            <input
                type="text"
                id="date_format"
                name="date_format"
                value="<?php echo htmlspecialchars($displayValues['date_format'], ENT_QUOTES, 'UTF-8'); ?>"
                maxlength="32"
                required
                placeholder="Y-m-d"
            >
            <small class="muted">PHP date format (e.g., Y-m-d, d/m/Y)</small>
        </div>

        <div class="form-group">
            <label for="time_format">Time Format <span class="required">*</span></label>
            <input
                type="text"
                id="time_format"
                name="time_format"
                value="<?php echo htmlspecialchars($displayValues['time_format'], ENT_QUOTES, 'UTF-8'); ?>"
                maxlength="32"
                required
                placeholder="H:i"
            >
            <small class="muted">PHP time format (e.g., H:i, h:i A)</small>
        </div>

        <div class="form-group">
            <label for="items_per_page">Items Per Page <span class="required">*</span></label>
            <input
                type="number"
                id="items_per_page"
                name="items_per_page"
                value="<?php echo htmlspecialchars($displayValues['items_per_page'], ENT_QUOTES, 'UTF-8'); ?>"
                min="5"
                max="100"
                required
            >
            <small class="muted">Number of items to display per page in listings (5-100)</small>
        </div>

        <div class="form-group">
            <button type="submit" class="btn primary">Save Settings</button>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
