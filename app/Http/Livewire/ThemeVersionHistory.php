<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Services\ThemeService;

class ThemeVersionHistory extends Component
{
    public Theme $theme;

    public function publishVersion($versionId)
    {
        $version = ThemeVersion::findOrFail($versionId);
        $this->authorize('update', $version->theme);

        $version->theme->versions()->update(['is_active' => false]);
        $version->update(['is_active' => true]);

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Version published successfully'
        ]);
    }

    public function exportVersion($versionId, $format)
    {
        $version = ThemeVersion::findOrFail($versionId);
        $this->authorize('view', $version->theme);

        $themeService = app(ThemeService::class);
        $exportPath = $themeService->exportVersion($version, $format);

        return response()->download($exportPath)
            ->deleteFileAfterSend(true);
    }

    public function exportAllVersions()
    {
        $this->authorize('view', $this->theme);

        $themeService = app(ThemeService::class);
        $exportPath = $themeService->exportAllVersions($this->theme);

        return response()->download($exportPath)
            ->deleteFileAfterSend(true);
    }

    public function setDefaultBranch($branchId)
    {
        $branch = $this->theme->branches()->findOrFail($branchId);
        $this->authorize('update', $this->theme);

        $this->theme->branches()->update(['is_default' => false]);
        $branch->update(['is_default' => true]);

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Default branch updated'
        ]);
    }

    public function toggleBranchProtection($branchId)
    {
        $branch = $this->theme->branches()->findOrFail($branchId);
        $this->authorize('update', $this->theme);

        $branch->update(['is_protected' => !$branch->is_protected]);

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => $branch->is_protected ? 'Branch protected' : 'Branch unprotected'
        ]);
    }

    public function render()
    {
        return view('livewire.theme-version-history');
    }
}
