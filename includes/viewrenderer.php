<?php
/**
 * View Renderer
 * Handles template rendering with layout support
 */
class ViewRenderer {
    public function render($viewPath, $data = [], $layoutPath = null) {
        $baseDir = __DIR__ . '/../'; // Project root from includes/

        extract($data);
        
        $fullViewPath = $baseDir . $viewPath;
        
        if ($layoutPath) {
            $fullLayoutPath = $baseDir . $layoutPath;
            ob_start();
            $base = realpath($baseDir);
            $target = realpath($fullViewPath);
            if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
                error_log("SECURITY: blocked dynamic include: view");
                ob_end_clean();
                return;
            }
            require_once $target;
            $content = ob_get_clean();

            $base = realpath($baseDir);
            $target = realpath($fullLayoutPath);
            if ($base !== false && $target !== false && substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) === 0 && is_file($target)) {
                require_once $target;
            } else {
                error_log("ViewRenderer: Layout file not found at {$fullLayoutPath}");
                echo "Layout file missing. "; // Output something if layout is missing
                echo $content; // Still output content
            }
        } else {
            $base = realpath($baseDir);
            $target = realpath($fullViewPath);
            if ($base !== false && $target !== false && substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) === 0 && is_file($target)) {
                require_once $target;
            } else {
                error_log("SECURITY: blocked dynamic include: view");
                echo "View file missing.";
            }
        }
    }
}
