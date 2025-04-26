<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\ThemeVersionRollback;

class ThemeVersionComparison extends Component
{
    public Theme $theme;
    public $version1;
    public $version2;
    public $comparisonData = [];
    public $isLoading = false;
    public $selectedFile = null;
    public $viewMode = 'side-by-side';
    public $syntaxHighlighting = true;
    public $rollbackHistory = [];
    public $showRollbackHistory = false;

    protected $queryString = [
        'version1' => ['except' => ''],
        'version2' => ['except' => '']
    ];

    public function mount(Theme $theme)
    {
        $this->theme = $theme;
    }

    public function compareVersions()
    {
        $this->validate([
            'version1' => 'required|exists:theme_versions,id',
            'version2' => 'required|exists:theme_versions,id'
        ]);

        $this->isLoading = true;

        try {
            $response = \Http::withToken(auth()->user()->currentAccessToken()->token)
                ->get(route('api.themes.versions.compare', [
                    'theme' => $this->theme->id,
                    'version1' => $this->version1,
                    'version2' => $this->version2
                ]));

            if ($response->successful()) {
                $this->comparisonData = $response->json();
                if (!empty($this->comparisonData['files']['modified'])) {
                    $this->selectedFile = array_key_first($this->comparisonData['files']['modified']);
                }
                $this->dispatch('diff-viewer-ready');
                
                // Update version tracking flags
                $version1 = ThemeVersion::find($this->version1);
                $version2 = ThemeVersion::find($this->version2);
                
                if ($version1 && $version2) {
                    // Mark newer version as latest
                    $newerVersion = $version1->created_at > $version2->created_at ? $version1 : $version2;
                    $olderVersion = $version1->created_at > $version2->created_at ? $version2 : $version1;
                    
                    $newerVersion->update(['is_latest' => true]);
                    $olderVersion->update([
                        'is_latest' => false,
                        'previous_version' => $newerVersion->id
                    ]);
                }
            } else {
                $this->addError('comparison', 'Failed to compare versions');
            }
        } catch (\Exception $e) {
            $this->addError('comparison', $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.theme-version-comparison', [
            'versions' => $this->theme->versions()->orderByDesc('created_at')->get(),
            'selectedFile' => $this->selectedFile,
            'diff' => $this->getCurrentDiff()
        ]);
    }

    protected function getCurrentDiff()
    {
        if (!$this->selectedFile || empty($this->comparisonData['files']['modified'][$this->selectedFile])) {
            return null;
        }

        $fileData = $this->comparisonData['files']['modified'][$this->selectedFile];
        
        return [
            'file' => $this->selectedFile,
            'old_version' => $this->comparisonData['version1'],
            'new_version' => $this->comparisonData['version2'],
            'old_content' => $fileData['old_content'] ?? '',
            'new_content' => $fileData['new_content'] ?? '',
            'diff' => $fileData['diff'] ?? []
        ];
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'side-by-side' ? 'inline' : 'side-by-side';
    }

    public function toggleSyntaxHighlighting()
    {
        $this->syntaxHighlighting = !$this->syntaxHighlighting;
    }

    public function performRollback()
    {
        $this->validate([
            'version1' => 'required|exists:theme_versions,id',
            'version2' => 'required|exists:theme_versions,id'
        ]);

        try {
            $response = \Http::withToken(auth()->user()->currentAccessToken()->token)
                ->post(route('api.themes.versions.rollback', [
                    'theme' => $this->theme->id,
                    'from_version' => $this->version1,
                    'to_version' => $this->version2
                ]));

            if ($response->successful()) {
                $this->dispatch('rollback-completed');
                $this->loadRollbackHistory();
            } else {
                $this->addError('rollback', 'Failed to perform rollback');
            }
        } catch (\Exception $e) {
            $this->addError('rollback', $e->getMessage());
        }
    }

    public function loadRollbackHistory()
    {
        if ($this->version1) {
            $this->rollbackHistory = ThemeVersionRollback::where('from_version_id', $this->version1)
                ->with(['from_version', 'to_version', 'user'])
                ->orderByDesc('created_at')
                ->get();
        }
    }

    public function updatedVersion1()
    {
        $this->loadRollbackHistory();
    }
}
