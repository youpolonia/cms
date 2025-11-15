<?php
namespace Core\Blocks;

use Core\PluginSandbox;

class BlockRenderer {
    public static function renderFrontend(string $blockKey, array $config): string {
        $handler = PluginBlockRegistry::getBlockHandler($blockKey, 'php');
        if (!$handler) {
            return "<!-- Block {$blockKey} has no PHP handler -->";
        }

        try {
            $sandbox = new PluginSandbox();
            ob_start();
            $sandbox->execute(function() use ($handler, $config) {
                [$class, $method] = explode('::', $handler);
                if (!class_exists($class)) {
                    throw new \RuntimeException("Handler class {$class} not found");
                }
                echo call_user_func([$class, $method], $config);
            });
            return ob_get_clean();
        } catch (\Throwable $e) {
            error_log("Block render failed: " . $e->getMessage());
            return "<!-- Block render error: {$blockKey} -->";
        }
    }

    public static function renderEditor(string $blockKey, array $config): string {
        $jsHandler = PluginBlockRegistry::getBlockHandler($blockKey, 'js');
        if ($jsHandler) {
            return "<div class='plugin-block' data-handler='{$jsHandler}' data-config='" . 
                   htmlspecialchars(json_encode($config), ENT_QUOTES) . "'></div>";
        }

        // Fallback to basic form if no JS handler
        $schema = PluginBlockRegistry::getBlockSchema($blockKey);
        if ($schema) {
            return self::renderSchemaForm($blockKey, $config, $schema);
        }

        return "<!-- No editor handler for block {$blockKey} -->";
    }

    private static function renderSchemaForm(string $blockKey, array $config, array $schema): string {
        // Basic form rendering from schema would be implemented here
        return "<div class='schema-form' data-block='{$blockKey}'>" . 
               "<!-- Schema-based form would be rendered here --></div>";
    }
}