<?php

if (!function_exists('get_theme_directory')) {
    function get_theme_directory(): string {
        return defined('THEME_DIR') ? THEME_DIR : 'themes/current';
    }
}

if (!function_exists('get_default_theme')) {
    function get_default_theme(): string {
        return defined('DEFAULT_THEME') ? DEFAULT_THEME : 'themes/default';
    }
}

if (!function_exists('get_tenant_widget_path')) {
    function get_tenant_widget_path(string $widget_name): string {
        $tenant_id = defined('TENANT_ID') ? TENANT_ID : 'default';
        return "widgets/$tenant_id/$widget_name";
    }
}

if (!function_exists('locate_template')) {
    function locate_template(string $widget_path): string {
        $theme_dir = get_theme_directory();
        $default_theme = get_default_theme();
        $template_path = "$theme_dir/widgets/$widget_path.php";
        
        if (!file_exists($template_path)) {
            $template_path = "$default_theme/widgets/$widget_path.php";
            if (!file_exists($template_path)) {
                error_log("Widget template not found in theme or default: $widget_path");
            }
        }
        
        return $template_path;
    }
}

if (!function_exists('render_theme_view')) {
    function render_theme_view(string $view_name, array $data = []): string {
        $theme_dir = get_theme_directory();
        $view_path = "$theme_dir/views/$view_name.php";
        
        if (!file_exists($view_path)) {
            error_log("Theme view not found: $view_name");
            return '';
        }
        
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            $base = realpath($theme_dir);
            $target = realpath($view_path);
            if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
                error_log("SECURITY: blocked dynamic include: theme view");
                ob_end_clean();
                return '';
            }
            require_once $target;
            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            error_log("Failed to render theme view: " . $e->getMessage());
            return '';
        }
    }
}

if (!function_exists('render_widget')) {
    function render_widget(string $widget_name, array $data = []): string {
        $widget_path = get_tenant_widget_path($widget_name);
        $template_path = locate_template($widget_path);
        
        if (!file_exists($template_path)) {
            error_log("Widget template not found: $widget_path");
            return render_theme_view('widgets/fallback', ['widget' => $widget_name]);
        }
        
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            $theme_dir = get_theme_directory();
            $base = realpath($theme_dir);
            $target = realpath($template_path);
            if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
                error_log("SECURITY: blocked dynamic include: widget template");
                ob_end_clean();
                return render_theme_view('widgets/error', [
                    'widget' => $widget_name,
                    'error' => 'Invalid widget path'
                ]);
            }
            require_once $target;
            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            error_log("Failed to render widget: " . $e->getMessage());
            return render_theme_view('widgets/error', [
                'widget' => $widget_name,
                'error' => $e->getMessage()
            ]);
        }
    }
}

if (!function_exists('render_widget_template')) {
    function render_widget_template(string $widget_path, array $data = []): string {
        $template_path = locate_template($widget_path);
        if (!file_exists($template_path)) {
            error_log("Widget template not found: $widget_path");
            return '';
        }
        
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            $theme_dir = get_theme_directory();
            $base = realpath($theme_dir);
            $target = realpath($template_path);
            if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
                error_log("SECURITY: blocked dynamic include: widget template");
                ob_end_clean();
                return '';
            }
            require_once $target;
            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            error_log("Failed to render widget template: " . $e->getMessage());
            return '';
        }
    }
}
