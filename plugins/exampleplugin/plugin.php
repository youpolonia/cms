<?php
namespace Plugins\ExamplePlugin;

use Includes\PluginInterface;

class ExamplePlugin implements PluginInterface {
    public function getName() {
        return 'ExamplePlugin';
    }

    public function initialize($pluginManager) {
        $initFn = require_once __DIR__ . '/bootstrap.php';
        if (is_callable($initFn)) {
            $initFn($pluginManager);
        }
    }
}

return new ExamplePlugin();
