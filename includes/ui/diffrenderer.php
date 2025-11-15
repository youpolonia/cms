<?php
/**
 * Version Comparison Diff Renderer
 * Formats diff results for UI display
 */

class DiffRenderer
{
    public function render(array $diff): string
    {
        if (empty($diff)) {
            return 'No changes detected';
        }

        $output = '
<div class="diff-container">';

        // Render removed lines
        if (!empty($diff['removed'])) {
            $output .= '
<div class="diff-section removed">';
            $output .= '
<h4>Removed Lines</h4>';
            foreach (
$diff['removed'] as $line) {
                $output .= '
<div class="diff-line removed">- ' . htmlspecialchars(
$line) . '</div>';
            }
            $output .= '</div>';
        }

        // Render added lines
        if (!empty($diff['added'])) {
            $output .= '
<div class="diff-section added">';
            $output .= '
<h4>Added Lines</h4>';
            foreach (
$diff['added'] as $line) {
                $output .= '
<div class="diff-line added">+ ' . htmlspecialchars(
$line) . '</div>';
            }
            $output .= '</div>';
        }

        // Render changed lines
        if (!empty($diff['changed'])) {
            $output .= '
<div class="diff-section changed">';
            $output .= '
<h4>Changed Lines</h4>';
            foreach (
$diff['changed'] as $change) {
                $output .= '
<div class="diff-line changed">';
                $output .= '
<div class="old-line">- ' . htmlspecialchars($change['old']) . '</div>';
                $output .= '
<div class="new-line">+ ' . htmlspecialchars(
$change['new']) . '</div>';
                $output .= '</div>';
            }
            $output .= '
</div>';
        }

        $output .= '</div>';

        return $output;
    }
}
