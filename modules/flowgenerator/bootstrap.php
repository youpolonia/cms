<?php
require_once __DIR__.'/flowregistry.php';
require_once __DIR__.'/flowtrigger.php';
require_once __DIR__.'/flowsender.php';

// Register CMS event listeners
register_event('layout_published', function($layoutId, $userId) {
    FlowTrigger::handleEvent('layout_published', [
        'layout_id' => $layoutId,
        'user_id' => $userId,
        'timestamp' => time()
    ]);
});

register_event('cpt_entry_created', function($entryId, $cptType) {
    FlowTrigger::handleEvent('cpt_entry_created', [
        'entry_id' => $entryId,
        'cpt_type' => $cptType,
        'timestamp' => time()
    ]);
});

register_event('theme_updated', function($themeName, $userId) {
    FlowTrigger::handleEvent('theme_updated', [
        'theme_name' => $themeName,
        'user_id' => $userId,
        'timestamp' => time()
    ]);
});

register_event('plugin_action', function($plugin, $action, $data) {
    FlowTrigger::handleEvent('plugin_action_'.$action, array_merge(
        ['plugin' => $plugin],
        $data
    ));
});
