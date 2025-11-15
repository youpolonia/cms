<?php
class HTMLDiff {
    public static function compare(string $oldHtml, string $newHtml): array {
        // Sanitize inputs first
        $oldHtml = self::sanitizeHtml($oldHtml);
        $newHtml = self::sanitizeHtml($newHtml);
        
        // First do structural comparison
        $structuralDiff = self::compareStructure($oldHtml, $newHtml);
        
        // Then do semantic comparison for changed elements
        $semanticDiff = [];
        foreach ($structuralDiff as $change) {
            if ($change['type'] === 'changed') {
                $semanticDiff[] = self::compareSemantics(
                    $change['old_content'],
                    $change['new_content']
                );
            }
        }
        
        return [
            'structural_changes' => $structuralDiff,
            'semantic_changes' => $semanticDiff
        ];
    }
    
    private static function sanitizeHtml(string $html): string {
        // Convert special chars to HTML entities
        $html = htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove event handlers and unsafe attributes
        $html = preg_replace('/\s+on\w+="[^"]*"/i', '', $html);
        
        return $html;
    }
    
    private static function compareStructure(string $old, string $new): array {
        $oldDom = new DOMDocument();
        @$oldDom->loadHTML($old);
        
        $newDom = new DOMDocument();
        @$newDom->loadHTML($new);
        
        $changes = [];
        self::compareNodes($oldDom->documentElement, $newDom->documentElement, $changes);
        return $changes;
    }
    
    private static function compareNodes(DOMNode $oldNode, DOMNode $newNode, array &$changes, string $path = '') {
        // Compare node types
        if ($oldNode->nodeType !== $newNode->nodeType) {
            $changes[] = [
                'type' => 'changed',
                'path' => $path,
                'old_type' => $oldNode->nodeType,
                'new_type' => $newNode->nodeType
            ];
            return;
        }
        
        // Compare node names for elements
        if ($oldNode->nodeType === XML_ELEMENT_NODE
            && $oldNode->nodeName !== $newNode->nodeName) {
            $changes[] = [
                'type' => 'changed',
                'path' => $path,
                'old_name' => $oldNode->nodeName,
                'new_name' => $newNode->nodeName
            ];
            return;
        }
        
        // Compare attributes for elements
        if ($oldNode->nodeType === XML_ELEMENT_NODE) {
            $oldAttrs = [];
            foreach ($oldNode->attributes as $attr) {
                $oldAttrs[$attr->name] = $attr->value;
            }
            
            $newAttrs = [];
            foreach ($newNode->attributes as $attr) {
                $newAttrs[$attr->name] = $attr->value;
            }
            
            $attrDiff = array_diff_assoc($newAttrs, $oldAttrs);
            if (!empty($attrDiff)) {
                $changes[] = [
                    'type' => 'changed',
                    'path' => $path,
                    'attribute_changes' => $attrDiff
                ];
            }
        }
        
        // Compare child nodes recursively
        $oldChildren = iterator_to_array($oldNode->childNodes);
        $newChildren = iterator_to_array($newNode->childNodes);
        
        $maxChildren = max(count($oldChildren), count($newChildren));
        for ($i = 0; $i < $maxChildren; $i++) {
            $childPath = $path ? "$path/$i" : "$i";
            
            if (!isset($oldChildren[$i])) {
                $changes[] = [
                    'type' => 'added',
                    'path' => $childPath,
                    'content' => $newChildren[$i]->textContent
                ];
            } elseif (!isset($newChildren[$i])) {
                $changes[] = [
                    'type' => 'removed',
                    'path' => $childPath,
                    'content' => $oldChildren[$i]->textContent
                ];
            } else {
                self::compareNodes($oldChildren[$i], $newChildren[$i], $changes, $childPath);
            }
        }
    }
    
    private static function compareSemantics(string $old, string $new): array {
        // Compare text content while ignoring HTML tags
        $oldText = strip_tags($old);
        $newText = strip_tags($new);
        
        return TextDiff::compare($oldText, $newText);
    }
}
