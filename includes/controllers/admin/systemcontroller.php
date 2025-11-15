<?php

namespace App\Controllers\Admin;

// Assuming a base controller or common functionalities might be used.
// If not, this can be a standalone class.
// use App\Controllers\BaseController; // Example if you have a base controller

class SystemController // Optionally extends BaseController
{
    /**
     * Displays the system status page.
     * Prepares data like PHP version and loads the status view.
     */
    public function status()
    {
        // Data to pass to the view
        $data = [
            'php_version' => phpversion(),
            'page_title' => 'System Status', // For the browser tab or layout header
            // Add more data as needed for the view
        ];

        // Load the view
        // This assumes a helper function or a method in a base controller to load views.
        // Adjust the path and method as per your application's structure.
        // For example, if your layout is handled by a function like `load_admin_view()`
        // or if the view itself includes the layout.

        // A common way to load a view within a layout:
        // 1. Set data
        // 2. Include the main layout file, which in turn includes the specific view file.

        // Let's assume a simple view loading mechanism for now.
        // The view 'admin/system/status.php' will be responsible for its content
        // and potentially including shared header/footer if not handled by a global layout mechanism.

        // If you have a function like `view('admin/system/status', $data)`:
        // view('admin/system/status', $data);

        // Or, if you directly require_once files and manage layout within controllers or a bootstrap file:
        // Define ADMIN_VIEWS_PATH if not already defined globally
        if (!defined('ADMIN_VIEWS_PATH')) {
            // Adjust this path based on your actual project structure
            // Assuming 'views' is at the root of your admin directory or similar
            define('ADMIN_VIEWS_PATH', __DIR__ . '/../../../admin/views/'); 
        }
        
        // Make data available to the view
        extract($data);

        // Include the layout, which will then require_once the content page
        // This is a common pattern: the layout includes header, sidebar, footer,
        // and a placeholder for the main content.
        if (file_exists(ADMIN_VIEWS_PATH . 'layout.php')) {
            // Pass the specific view file to the layout
            $contentView = __DIR__ . '/../../../admin/system/status.php';
            // Ensure $contentView is accessible within layout.php
            require_once ADMIN_VIEWS_PATH . 'layout.php';
        } else {
            // Fallback if layout.php is not found or not used this way
            // You might directly require_once the view or handle error
            if (file_exists(__DIR__ . '/../../../admin/system/status.php')) {
                require_once __DIR__ . '/../../../admin/system/status.php';
            } else {
                // Handle error: view file not found
                echo "Error: System status view file not found.";
                // Potentially log this error or show a more user-friendly message
            }
        }
    }

    /**
     * Alias for status() if 'index' is preferred as the default method.
     */
    public function index()
    {
        $this->status();
    }
}
