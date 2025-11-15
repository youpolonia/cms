<?php
// HelloWorld plugin bootstrap file

return function($pluginManager) {
    $pluginManager->addHook('init', 'hello_world_init');
    $pluginManager->addFilter('content_before_render', 'hello_world_content_filter');
};
