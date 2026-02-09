<?php
/**
 * JTB Theme Settings Controller
 * Handles the theme settings admin page
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Use CMS session boot
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

// Check authentication
if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
    header('Location: /admin/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Plugin path
$pluginPath = dirname(__DIR__);

// Load dependencies
require_once $pluginPath . '/includes/class-jtb-theme-settings.php';
require_once $pluginPath . '/includes/class-jtb-css-generator.php';
require_once $pluginPath . '/includes/class-jtb-style-system.php';
require_once $pluginPath . '/includes/class-jtb-element.php';

// Create table if not exists
JTB_Theme_Settings::createTable();

// Handle actions
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'export':
        handleExport();
        break;

    case 'import':
        handleImport();
        break;

    case 'reset':
        handleReset();
        break;

    default:
        showSettingsPage($pluginPath);
        break;
}

/**
 * Show the main settings page
 */
function showSettingsPage(string $pluginPath): void
{
    $settings = JTB_Theme_Settings::getAll();
    $defaults = JTB_Theme_Settings::getDefaults();
    $groupLabels = JTB_Theme_Settings::getGroupLabels();
    $csrfToken = $_SESSION['csrf_token'];
    $pluginUrl = '/plugins/jessie-theme-builder';

    // Get font options from JTB_Element
    $fontOptions = getFontOptions();

    require $pluginPath . '/views/theme-settings.php';
}

/**
 * Handle settings export
 */
function handleExport(): void
{
    $json = JTB_Theme_Settings::export();

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="jtb-theme-settings-' . date('Y-m-d') . '.json"');
    echo $json;
    exit;
}

/**
 * Handle settings import
 */
function handleImport(): void
{
    // Validate CSRF
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $_SESSION['jtb_message'] = ['type' => 'error', 'text' => 'Invalid security token'];
        header('Location: /admin/jtb/theme-settings');
        exit;
    }

    if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['jtb_message'] = ['type' => 'error', 'text' => 'No file uploaded'];
        header('Location: /admin/jtb/theme-settings');
        exit;
    }

    $json = file_get_contents($_FILES['import_file']['tmp_name']);

    if (JTB_Theme_Settings::import($json)) {
        $_SESSION['jtb_message'] = ['type' => 'success', 'text' => 'Settings imported successfully'];
    } else {
        $_SESSION['jtb_message'] = ['type' => 'error', 'text' => 'Failed to import settings. Invalid JSON file.'];
    }

    header('Location: /admin/jtb/theme-settings');
    exit;
}

/**
 * Handle settings reset
 */
function handleReset(): void
{
    // Validate CSRF
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $_SESSION['jtb_message'] = ['type' => 'error', 'text' => 'Invalid security token'];
        header('Location: /admin/jtb/theme-settings');
        exit;
    }

    $group = $_GET['group'] ?? null;

    if ($group) {
        if (JTB_Theme_Settings::resetGroup($group)) {
            $_SESSION['jtb_message'] = ['type' => 'success', 'text' => ucfirst($group) . ' settings reset to defaults'];
        } else {
            $_SESSION['jtb_message'] = ['type' => 'error', 'text' => 'Failed to reset settings'];
        }
    } else {
        if (JTB_Theme_Settings::resetAll()) {
            $_SESSION['jtb_message'] = ['type' => 'success', 'text' => 'All settings reset to defaults'];
        } else {
            $_SESSION['jtb_message'] = ['type' => 'error', 'text' => 'Failed to reset settings'];
        }
    }

    header('Location: /admin/jtb/theme-settings');
    exit;
}

/**
 * Get font options for select fields
 */
function getFontOptions(): array
{
    return [
        // System Fonts
        '' => '-- System Fonts --',
        'inherit' => 'Inherit',
        'Arial' => 'Arial',
        'Helvetica' => 'Helvetica',
        'Georgia' => 'Georgia',
        'Times New Roman' => 'Times New Roman',
        'Verdana' => 'Verdana',
        'system-ui' => 'System UI',

        // Google Fonts - Popular
        '_google' => '-- Google Fonts --',
        'Inter' => 'Inter',
        'Roboto' => 'Roboto',
        'Open Sans' => 'Open Sans',
        'Lato' => 'Lato',
        'Montserrat' => 'Montserrat',
        'Poppins' => 'Poppins',
        'Raleway' => 'Raleway',
        'Nunito' => 'Nunito',
        'Work Sans' => 'Work Sans',
        'DM Sans' => 'DM Sans',
        'Plus Jakarta Sans' => 'Plus Jakarta Sans',
        'Outfit' => 'Outfit',
        'Manrope' => 'Manrope',
        'Space Grotesk' => 'Space Grotesk',

        // Serif
        '_serif' => '-- Serif --',
        'Playfair Display' => 'Playfair Display',
        'Merriweather' => 'Merriweather',
        'Lora' => 'Lora',
        'PT Serif' => 'PT Serif',
        'Libre Baskerville' => 'Libre Baskerville',
        'Crimson Text' => 'Crimson Text',
        'EB Garamond' => 'EB Garamond',
        'DM Serif Display' => 'DM Serif Display',
        'Fraunces' => 'Fraunces',

        // Display
        '_display' => '-- Display --',
        'Oswald' => 'Oswald',
        'Bebas Neue' => 'Bebas Neue',
        'Anton' => 'Anton',
        'Abril Fatface' => 'Abril Fatface',
        'Lobster' => 'Lobster',
        'Pacifico' => 'Pacifico',

        // Monospace
        '_mono' => '-- Monospace --',
        'Roboto Mono' => 'Roboto Mono',
        'Source Code Pro' => 'Source Code Pro',
        'Fira Code' => 'Fira Code',
        'JetBrains Mono' => 'JetBrains Mono'
    ];
}
