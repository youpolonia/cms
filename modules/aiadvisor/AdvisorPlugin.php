<?php
use includes\ThemeManager;

class AdvisorPlugin {
    public static function init(): void {
        // Register with Builder Engine
        require_once __DIR__.'/advisorinterface.php';
        AdvisorInterface::registerWithBuilder();

        // Add admin UI hooks
        self::addAdminHooks();
    }

    private static function addAdminHooks(): void {
        AdminUI::addToolbarButton('design-analyzer', [
            'label' => 'Analyze Design',
            'icon' => 'palette',
            'action' => 'runDesignAnalysis'
        ]);

        AdminUI::registerAction('runDesignAnalysis', function() {
            $layout = BuilderEngine::getCurrentLayout();
            $theme = \includes\ThemeManager::getActiveTheme();
            
            $analysis = AdvisorInterface::analyzeLayout($layout, $theme);
            return AdminUI::renderAnalysisPanel($analysis);
        });
    }

    public static function getAnalysisEndpoint(): string {
        return '/api/design-advisor/analyze';
    }
}
