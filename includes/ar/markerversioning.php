<?php
declare(strict_types=1);

require_once __DIR__ . '/markermanager.php';
require_once __DIR__ . '/../diffengine.php';

class MarkerVersioning {
    public static function getVersionDiff(
        int $versionId1, 
        int $versionId2,
        string $format = 'html'
    ): array {
        $comparison = MarkerManager::compareVersions($versionId1, $versionId2);
        
        return [
            'diff' => self::formatDiff($comparison['diff'], $format),
            'metadata' => $comparison['metadata']
        ];
    }

    private static function formatDiff(array $diff, string $format): string {
        switch ($format) {
            case 'html':
                return self::generateHtmlDiff($diff);
            case 'json':
                return json_encode($diff, JSON_PRETTY_PRINT);
            case 'text':
                return self::generateTextDiff($diff);
            default:
                throw new InvalidArgumentException('Invalid diff format');
        }
    }

    private static function generateHtmlDiff(array $diff): string {
        $html = '
<div class="marker-diff">';
        foreach ($diff as $key => $changes) {
            $html .= sprintf('
<div class="diff-section"><h3>%s</h3>', htmlspecialchars(
$key));
            
            if (isset($changes['old'])) {
                $html .= sprintf('
<div class="diff-old">-%s</div>', 
                    htmlspecialchars(
$changes['old']));
            }
            if (isset($changes['new'])) {
                $html .= sprintf('
<div class="diff-new">+%s</div>',
                    htmlspecialchars(
$changes['new']));
            }
            
            $html .= '
</div>';
        }
        return $html . '</div>';
    }

    private static function generateTextDiff(array $diff): string {
        $text = '';
        foreach ($diff as $key => $changes) {
            $text .= "$key:\n";
            if (isset($changes['old'])) {
                $text .= "- {$changes['old']}\n";
            }
            if (isset($changes['new'])) {
                $text .= "+ {$changes['new']}\n";
            }
            $text .= "\n";
        }
        return trim($text);
    }
}
