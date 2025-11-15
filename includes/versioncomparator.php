<?php

use PDO;
use PDOException;
use RuntimeException;

class VersionComparator {
    public function compare($html1, $html2) {
        $dom1 = new DOMDocument();
        @$dom1->loadHTML($html1);
        $dom2 = new DOMDocument();
        @$dom2->loadHTML($html2);

        $lines1 = [];
        $lines2 = [];

        $this->generateCanonicalLines($dom1->documentElement, $lines1);
        $this->generateCanonicalLines($dom2->documentElement, $lines2);

        $diff = $this->computeDiff($lines1, $lines2);

        $report = [];
        foreach ($diff as $change) {
            if ($change['type'] == 'deleted') {
                $report[] = '- ' . $change['line'];
            } elseif ($change['type'] == 'inserted') {
                $report[] = '+ ' . $change['line'];
            } else {
                $report[] = '  ' . $change['line'];
            }
        }

        return implode("\n", $report);
    }

    private function generateCanonicalLines(DOMNode $node, array &$lines) {
        if ($node instanceof DOMElement) {
            $tag = $node->tagName;
            $attributes = [];
            foreach ($node->attributes as $name => $attr) {
                $attributes[$name] = $attr->value;
            }
            ksort($attributes);
            $attrStr = '';
            foreach ($attributes as $name => $value) {
                $attrStr .= " $name=\"$value\"";
            }
            $lines[] = "<$tag" . $attrStr . ">";
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    $this->generateCanonicalLines($child, $lines);
                }
            }
            $lines[] = "</$tag>";
        } elseif ($node instanceof DOMText) {
            $text = trim($node->wholeText);
            if ($text !== '') {
                $text = preg_replace('/\s+/', ' ', $text);
                $lines[] = $text;
            }
        }
    }

    private function computeDiff(array $a, array $b) {
        $i = 0;
        $j = 0;
        $diff = [];

        while ($i < count($a) || $j < count($b)) {
            if ($i < count($a) && $j < count($b) && $a[$i] == $b[$j]) {
                $diff[] = ['type' => 'unchanged', 'line' => $a[$i]];
                $i++;
                $j++;
            } else {
                if ($i < count($a)) {
                    $diff[] = ['type' => 'deleted', 'line' => $a[$i]];
                    $i++;
                }
                if ($j < count($b)) {
                    $diff[] = ['type' => 'inserted', 'line' => $b[$j]];
                    $j++;
                }
            }
        }

        return $diff;
    }

    /**
     * Restores content from a specific version
     * @param int $versionId The version ID to restore
     * @param PDO $pdo Database connection
     * @return string The restored content
     * @throws PDOException If database operation fails
     * @throws RuntimeException If version not found
     */
    public function restoreVersion(int $versionId, PDO $pdo): string {
        $stmt = $pdo->prepare("
            SELECT content_data
            FROM content_versions
            WHERE id = :version_id
        ");
        $stmt->bindParam(':version_id', $versionId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new RuntimeException("Version $versionId not found");
        }
        
        return $result['content_data'];
    }
}
