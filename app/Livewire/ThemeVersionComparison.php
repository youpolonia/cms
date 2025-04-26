<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ThemeVersion;
use App\Services\VersionComparisonService;

class ThemeVersionComparison extends Component
{
    public ThemeVersion $oldVersion;
    public ThemeVersion $newVersion;
    public array $comparison;
    public ?string $selectedFile = null;
    public array $fileDiff = [];

    public function mount(ThemeVersion $oldVersion, ThemeVersion $newVersion)
    {
        $this->oldVersion = $oldVersion;
        $this->newVersion = $newVersion;
        $this->comparison = app(VersionComparisonService::class)
            ->compareVersions($oldVersion, $newVersion);
    }

    public function selectFile(string $filePath)
    {
        $this->selectedFile = $filePath;
        $this->fileDiff = app(VersionComparisonService::class)
            ->getFileDiff($filePath, $this->oldVersion, $this->newVersion);
    }

    public function render()
    {
        return view('livewire.theme-version-comparison');
    }
}
