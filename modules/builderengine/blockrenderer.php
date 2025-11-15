<?php
class BlockRenderer {
    public static function render(array $block, ?string $context = null): string {
        // Allow plugins to modify block before rendering
        $block = PluginManager::dispatchFilter('onBeforeRenderBlock', $block, [
            'context' => $context
        ]);

        $html = ''; // Original rendering logic here

        // Allow plugins to modify rendered HTML
        return PluginManager::dispatchFilter('onAfterRenderBlock', $html, [
            'block' => $block,
            'context' => $context
        ]);
    }

    public static function renderLayout(array $blocks, ?string $context = null): string {
        // Allow plugins to modify blocks before layout rendering
        $blocks = PluginManager::dispatchFilter('onBeforeRenderLayout', $blocks, [
            'context' => $context
        ]);

        $html = ''; // Original layout rendering logic here

        // Allow plugins to modify final layout HTML
        return PluginManager::dispatchFilter('onAfterRenderLayout', $html, [
            'blocks' => $blocks,
            'context' => $context
        ]);
    }
}
