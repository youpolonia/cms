<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\ContentDiffService;

class VersionComparison extends Component
{
    public $oldVersion;
    public $newVersion;
    public $diffs;

    public function __construct($oldVersion, $newVersion, ContentDiffService $diffService)
    {
        $this->oldVersion = $oldVersion;
        $this->newVersion = $newVersion;
        $this->diffs = $diffService->compareVersions($oldVersion, $newVersion);
    }

    public function render()
    {
        return view('components.version-comparison');
    }
}