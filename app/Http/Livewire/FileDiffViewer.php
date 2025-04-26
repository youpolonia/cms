<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ThemeVersion;
use App\Services\VersionComparisonService;
use App\Services\FileModificationService;

class FileDiffViewer extends Component
{
    public $diff = [];
    public $viewMode = 'side-by-side';
    public $syntaxHighlighting = true;
    public $resolvedLines = [];
    public $bulkAction = 'none';
    public $showLineNumbers = true;
    public $highlightMode = 'word'; // 'word' or 'line'
    public $showWhitespaceChanges = false;

    protected $listeners = [
        'diff-viewer-ready' => 'refreshDiff',
        'resolve-conflict' => 'resolveConflict',
        'toggle-line-numbers' => 'toggleLineNumbers',
        'toggle-highlight-mode' => 'toggleHighlightMode',
        'toggle-whitespace' => 'toggleWhitespaceChanges'
    ];

    public function refreshDiff()
    {
        $this->diff = $this->diff;
        $this->resolvedLines = [];
    }

    public function resolveConflict($data)
    {
        $lineNumber = $data['line'];
        $action = $data['action'];
        
        $this->resolvedLines[$lineNumber] = $action;
        
        if ($action === 'accept') {
            // Find and apply the change
            foreach ($this->diff['diff'] as &$line) {
                if ($line['line_number'] === $lineNumber) {
                    if ($line['type'] === 'added') {
                        $line['applied'] = true;
                    } elseif ($line['type'] === 'removed') {
                        $line['applied'] = false;
                    }
                    break;
                }
            }
        } elseif ($action === 'reject') {
            // Revert the change
            foreach ($this->diff['diff'] as &$line) {
                if ($line['line_number'] === $lineNumber) {
                    if ($line['type'] === 'added') {
                        $line['applied'] = false;
                    } elseif ($line['type'] === 'removed') {
                        $line['applied'] = true;
                    }
                    $this->resolvedLines[$line['line_number']] = $this->bulkAction === 'accept-all' ? 'accept' : 'reject';
                    break;
                }
            }
        }
        
        $this->dispatch('resolution-applied', line: $lineNumber, action: $action);
    }

    public function saveChanges(FileModificationService $fileModService)
    {
        // Create backup first
        $backupPath = $fileModService->createBackup($this->diff['file']);
        if (!$backupPath) {
            $this->dispatch('save-error', message: 'Failed to create backup file');
            return;
        }

        // Apply resolutions
        $success = $fileModService->applyResolutions(
            $this->diff['file'],
            $this->diff,
            $this->resolvedLines
        );

        if ($success) {
            $this->dispatch('save-success', message: 'Changes saved successfully');
        } else {
            $this->dispatch('save-error', message: 'Failed to save changes');
        }
    }

    public function applyBulkAction()
    {
        foreach ($this->diff['diff'] ?? [] as &$line) {
            if (!in_array($line['type'], ['added', 'removed'])) {
                continue;
            }

            if ($this->bulkAction === 'accept-all') {
                $this->resolvedLines[$line['line_number']] = 'accept';
                $line['applied'] = $line['type'] === 'added';
            } elseif ($this->bulkAction === 'reject-all') {
                $this->resolvedLines[$line['line_number']] = 'reject';
                $line['applied'] = $line['type'] === 'removed';
            }
        }
        
        $this->bulkAction = 'none';
        $this->dispatch('bulk-resolution-applied');
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'side-by-side' ? 'inline' : 'side-by-side';
    }

    public function toggleSyntaxHighlighting()
    {
        $this->syntaxHighlighting = !$this->syntaxHighlighting;
    }

    public function toggleLineNumbers()
    {
        $this->showLineNumbers = !$this->showLineNumbers;
    }

    public function toggleHighlightMode()
    {
        $this->highlightMode = $this->highlightMode === 'word' ? 'line' : 'word';
    }

    public function toggleWhitespaceChanges()
    {
        $this->showWhitespaceChanges = !$this->showWhitespaceChanges;
    }

    public function render()
    {
        return view('livewire.file-diff-viewer');
    }
}
