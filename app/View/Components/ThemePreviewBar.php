<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\ThemeService;

class ThemePreviewBar extends Component
{
    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function isPreviewing()
    {
        return $this->themeService->isPreviewing();
    }

    public function getPreviewTheme()
    {
        return $this->themeService->getPreviewTheme();
    }

    public function getOriginalTheme()
    {
        return $this->themeService->getOriginalTheme();
    }

    public function getPreviewTimeLeft()
    {
        return $this->themeService->getPreviewTimeLeft();
    }

    public function hasMarketplaceUpdate()
    {
        return $this->themeService->hasMarketplaceUpdate();
    }

    public function render()
    {
        return view('components.theme-preview-bar');
    }
}
