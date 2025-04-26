<?php

namespace App\View\Components;

use App\Models\ContentVersionDiff;
use Illuminate\View\Component;

class ContentDiffViewer extends Component
{
    public ContentVersionDiff $diff;

    public function __construct(ContentVersionDiff $diff)
    {
        $this->diff = $diff;
    }

    public function render()
    {
        return view('components.content-diff-viewer');
    }

    public function changes(): array
    {
        $data = json_decode($this->diff->diff_content, true);
        return $data['line_diffs'] ?? $data['changes'] ?? [];
    }

    public function similarity(): float
    {
        return json_decode($this->diff->diff_content, true)['similarity'] ?? 0;
    }

    public function isLineDiff(): bool
    {
        $data = json_decode($this->diff->diff_content, true);
        return isset($data['line_diffs']);
    }

    public function hasFieldChanges(): bool
    {
        $data = json_decode($this->diff->diff_content, true);
        return isset($data['changes']);
    }
}