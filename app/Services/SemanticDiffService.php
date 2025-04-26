<?php

namespace App\Services;

use cebe\markdown\GithubMarkdown;
use Diff\DiffOp\DiffOpAdd;
use Diff\DiffOp\DiffOpRemove;
use Diff\DiffOp\DiffOpChange;
use Diff\Patcher\MapPatcher;

class SemanticDiffService
{
    protected GithubMarkdown $markdown;

    public function __construct()
    {
        $this->markdown = new GithubMarkdown();
    }

    public function compareVersions(array $oldData, array $newData): array
    {
        $patcher = new MapPatcher();
        $diff = $patcher->patch($oldData, $newData);

        return $this->formatDiff($diff);
    }

    protected function formatDiff(array $diff): array
    {
        $result = [];
        
        foreach ($diff as $key => $op) {
            if ($op instanceof DiffOpAdd) {
                $result[$key] = [
                    'type' => 'added',
                    'value' => $op->getNewValue()
                ];
            } elseif ($op instanceof DiffOpRemove) {
                $result[$key] = [
                    'type' => 'removed',
                    'value' => $op->getOldValue()
                ];
            } elseif ($op instanceof DiffOpChange) {
                $result[$key] = [
                    'type' => 'changed',
                    'old' => $op->getOldValue(),
                    'new' => $op->getNewValue()
                ];
            }
        }

        return $result;
    }

    public function compareMarkdown(string $oldContent, string $newContent): array
    {
        $oldHtml = $this->markdown->parse($oldContent);
        $newHtml = $this->markdown->parse($newContent);
        
        return $this->compareVersions(
            ['html' => $oldHtml],
            ['html' => $newHtml]
        );
    }
}