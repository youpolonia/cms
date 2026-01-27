<?php
namespace TestBlockPlugin;

use CMS\Plugins\EnhancedPluginInterface;
use Core\HookManager;

class TestBlockPlugin implements EnhancedPluginInterface {
    private HookManager $hookManager;

    public function __construct(HookManager $hookManager) {
        $this->hookManager = $hookManager;
        error_log("TestBlockPlugin initialized");
    }

    public function getMetadata(): array {
        return [
            'name' => 'Test Block Plugin',
            'version' => '1.0.0',
            'author' => 'CMS Test Suite',
            'description' => 'Test plugin for block system validation'
        ];
    }

    public function getDependencies(): array {
        return [];
    }

    public function getVersionCompatibility(): array {
        return [];
    }

    public function init(): void {
        error_log("TestBlockPlugin init");
    }

    public function install(): void {
        error_log("TestBlockPlugin installed");
    }

    public function activate(): void {
        error_log("TestBlockPlugin activated");
        $this->registerHooks();
    }

    public function deactivate(): void {
        error_log("TestBlockPlugin deactivated");
    }

    public function uninstall(): void {
        error_log("TestBlockPlugin uninstalled");
    }

    public function registerHooks(): void {
        error_log("Registering TestBlockPlugin hooks");
        $this->hookManager->addAction('register_blocks', [$this, 'registerBlocks']);
    }

    public function getHookPoints(): array {
        return ['register_blocks' => 10];
    }

    public function registerBlocks(): void {
        error_log("Registering test blocks");
        
        $manifest = json_decode(file_get_contents(__DIR__.'/manifest.json'), true);
        if (!$manifest) {
            error_log("Failed to load manifest.json");
            return;
        }

        foreach ($manifest['blocks'] as $blockName => $blockConfig) {
            error_log("Registering block: $blockName");
            
            $this->hookManager->addFilter('register_block_type', function($blocks) use ($blockName, $blockConfig) {
                $blocks[$blockName] = [
                    'label' => $blockConfig['label'],
                    'category' => $blockConfig['category'],
                    'render_callback' => [$this, 'renderBlock'],
                    'editor_script' => $this->getEditorScript(),
                    'editor_style' => $this->getEditorStyle(),
                    'schema' => $blockConfig['schema']
                ];
                return $blocks;
            });
        }
    }

    public function renderBlock(array $config, string $content): string {
        error_log("Rendering block with config: ".json_encode($config));
        
        $template = file_get_contents(__DIR__.'/templates/frontend.html');
        foreach ($config as $key => $value) {
            $template = str_replace("{{ config.$key }}", htmlspecialchars($value), $template);
        }
        $template = str_replace("{{ now|date('Y-m-d H:i:s') }}", date('Y-m-d H:i:s'), $template);
        
        return $template;
    }

    private function getEditorScript(): string {
        return file_get_contents(__DIR__.'/templates/editor.html');
    }

    private function getEditorStyle(): string {
        return '.test-block-editor { padding: 10px; border: 1px dashed #ccc; }';
    }
}
