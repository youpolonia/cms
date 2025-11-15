<?php
namespace Example;

use CMS\Plugins\PluginInterface;
use CMS\Plugins\HookManager;

class ExamplePlugin implements PluginInterface
{
    private HookManager $hookManager;

    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Example Plugin',
            'version' => '1.0.0',
            'author' => 'CMS Team',
            'description' => 'Demonstration plugin for the CMS plugin system'
        ];
    }

    public function init(): void
    {
        // Plugin initialization logic
        $this->hookManager->addAction('admin_menu', [$this, 'addAdminMenu']);
        $this->hookManager->addFilter('the_content', [$this, 'filterContent']);
    }

    public function registerHooks(): void
    {
        $this->hookManager->addAction('admin_menu', [$this, 'addAdminMenu']);
        $this->hookManager->addFilter('the_content', [$this, 'filterContent']);
    }

    public function addAdminMenu(): void
    {
        // Example admin menu addition
        add_menu_page(
            'Example Plugin',
            'Example',
            'manage_options',
            'example-plugin',
            [$this, 'renderAdminPage']
        );
    }

    public function filterContent(string $content): string
    {
        // Example content filter
        return str_replace(
            'Example',
            '<strong>Example</strong>',
            $content
        );
    }

    public function renderAdminPage(): void
    {
        // Simple admin page rendering
        echo '
<div class="wrap">';
        echo '
<h1>Example Plugin Settings</h1>';
        echo '
<p>This is an example plugin admin page.</p>';
        echo '</div>';
    }
}
