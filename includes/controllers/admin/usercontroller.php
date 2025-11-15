<?php
require_once __DIR__ . '/../../../core/csrf.php';

namespace Includes\Controllers\Admin;

class UserController {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        // In a real application, you would fetch users from the database here.
        // $users = UserModel::getAll(); // Example
        $users = []; // Placeholder

        // Path to the view file
        $viewPath = __DIR__ . '/../../../../admin/users/index.php';

        $base = realpath(__DIR__ . '/../../../../admin/users');
        $target = realpath($viewPath);
        if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
            error_log("SECURITY: blocked dynamic include: user index view");
            return;
        }
        require_once $target;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        // Path to the view file
        $viewPath = __DIR__ . '/../../../../admin/users/create.php';

        $base = realpath(__DIR__ . '/../../../../admin/users');
        $target = realpath($viewPath);
        if ($base !== false && $target !== false && substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) === 0 && is_file($target)) {
            // Include the view. The view itself handles the layout or provides content for it.
            require_once $target;
        } else {
            // Handle view not found error
            error_log("SECURITY: blocked dynamic include: user view");
            echo "Error: Create user form view not found.";
            // Potentially log this error
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store() {
        csrf_validate_or_403();
        // For now, just dump the POST data
        echo '<pre>';
        var_dump($_POST);
        echo '</pre>';

        // Display a success message (or handle actual data saving and redirect)
        echo "<p>User data submitted (not saved yet).</p>";
        echo '<a href="' . APP_URL . '/admin/users">Back to User List</a>';
        // In a real application, you would validate data, save to database,
        // and then redirect, e.g.:
        // header('Location: ' . APP_URL . '/admin/users?status=user_created');
        // exit;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return void
     */
    public function edit($id) {
        // Simulate fetching a user by ID. Replace with actual database query.
        $user = [
            'id' => $id,
            'username' => 'testuser' . $id,
            'email' => 'testuser' . $id . '@example.com',
            'role' => ($id % 2 == 0) ? 'admin' : 'editor' // Example role logic
        ];

        // Path to the view file
        $viewPath = __DIR__ . '/../../../../admin/users/edit.php';

        $base = realpath(__DIR__ . '/../../../../admin/users');
        $target = realpath($viewPath);
        if ($base !== false && $target !== false && substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) === 0 && is_file($target)) {
            // Make $user available to the view
            // In a more structured View class, you'd pass $user to a render method.
            // For now, we can extract or simply make it available in the current scope.
            // extract(['user' => $user]); // This would make $user available directly
            require_once $target; // The view will use the $user variable passed to it or defined globally
        } else {
            error_log("SECURITY: blocked dynamic include: user view");
            echo "Error: Edit user form view not found.";
            // Potentially log this error
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return void
     */
    public function update($id) {
        csrf_validate_or_403();
        echo "<h1>Updating User ID: " . htmlspecialchars($id) . "</h1>";
        echo '<pre>';
        echo "Form Data (POST):\n";
        var_dump($_POST);
        echo '</pre>';

        // Simulate a success message or redirect
        echo "<p>User data for ID {$id} submitted (not actually updated yet).</p>";

        // Define APP_URL if not already defined (for standalone testing or if not set globally yet)
        if (!defined('APP_URL')) {
            // Attempt to determine APP_URL, adjust as necessary for your environment
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $scriptName = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
            define('APP_URL', rtrim($protocol . $host . $scriptName, '/'));
        }

        echo '<a href="' . APP_URL . '/admin/users">Back to User List</a>';
        // In a real application:
        // Validate data
        // Update user in database
        // Redirect to user list or edit page with a success message
        // header('Location: ' . APP_URL . '/admin/users?status=user_updated');
        // exit;
    }

    /**
     * Simulate removing the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function destroy($id) {
        csrf_validate_or_403();
        // Simulate user deletion
        // In a real application, you would delete the user from the database here.
        // For now, we'll set a session message or similar to indicate action.
        // Session handling would ideally be in a dedicated service.
        // Session is already started via core bootstrap
        $_SESSION['flash_message'] = "User with ID: " . htmlspecialchars($id) . " would be deleted (simulation).";

        // Define APP_URL if not already defined (for standalone testing or if not set globally yet)
        // This should ideally be defined globally, e.g., in a bootstrap or config file.
        if (!defined('APP_URL')) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            // Adjust scriptName to correctly point to the application root if routes are handled by a front controller
            // For now, assuming admin scripts are directly accessed or routing handles this.
            $scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // Get directory of current script
             // Attempt to find a common base path if SCRIPT_NAME is deep within admin structure
            $basePath = '';
            if (strpos($scriptName, '/admin/users') !== false) { // Example check
                $basePath = substr($scriptName, 0, strpos($scriptName, '/admin'));
            } elseif (strpos($scriptName, '/admin') !== false) {
                 $basePath = substr($scriptName, 0, strpos($scriptName, '/admin'));
            } else {
                // Fallback if not in admin, or adjust as per your app structure
                // This might need refinement based on how your APP_URL is consistently determined
                $basePath = $scriptName;
            }
            define('APP_URL', rtrim($protocol . $host . $basePath, '/'));
        }

        // Redirect back to the user list
        header('Location: ' . APP_URL . '/admin/users');
        exit;
    }
}
