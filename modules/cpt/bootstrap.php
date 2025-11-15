<?php
require_once __DIR__ . '/BuilderCore.php';
/**
 * CPT Module Bootstrap
 */

defined('CMS_ROOT') or die('Direct access denied');

// Initialize registry and renderer
$registry = CPTRegistry::getInstance();
$renderer = new CPTRenderer();

// Register with service container
$container = ServiceContainer::getInstance();
$container->set('cpt.registry', $registry);
$container->set('cpt.renderer', $renderer);

// Register with Builder Engine
BuilderCore::checkDependencies();
BuilderCore::registerBlocks([
    [
        'name' => 'cpt',
        'label' => 'Custom Post Type',
        'template' => __DIR__ . '/templates/cpt-block.php',
        'fields' => [
            'type' => ['type' => 'select', 'options' => array_keys($registry->getAll())],
            'id' => ['type' => 'text']
        ]
    ]
]);

// Register admin interface if in admin area
if (defined('CMS_ADMIN')) {
    // Add admin menu items here
}
