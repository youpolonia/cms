<?php
/**
 * Base documentation parser
 */
abstract class DocParser {
    abstract public function supports(string $filePath): bool;
    abstract public function parse(string $content): array;
}

class PhpDocParser extends DocParser {
    public function supports(string $filePath): bool {
        return pathinfo($filePath, PATHINFO_EXTENSION) === 'php';
    }

    public function parse(string $content): array {
        $tokens = token_get_all($content);
        $docs = [];
        $currentClass = null;

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            
            if ($token[0] === T_CLASS) {
                $currentClass = $this->parseClass($tokens, $i);
                $docs[$currentClass['name']] = $currentClass;
            } elseif ($token[0] === T_DOC_COMMENT) {
                $parsed = $this->parseDocBlock($token[1]);
                if ($currentClass) {
                    $docs[$currentClass['name']]['methods'][] = $parsed;
                } else {
                    $docs['__global'][] = $parsed;
                }
            }
        }
        return $docs;
    }

    private function parseClass(array &$tokens, int &$i): array {
        $class = ['name' => '', 'methods' => []];
        
        // Skip whitespace
        while ($tokens[++$i][0] === T_WHITESPACE);
        
        $class['name'] = $tokens[$i][1];
        return $class;
    }

    private function parseDocBlock(string $comment): array {
        $lines = explode("\n", $comment);
        $result = ['description' => '', 'tags' => []];
        $currentTag = null;

        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*/");
            if (empty($line)) continue;

            if (str_starts_with($line, '@')) {
                $parts = preg_split('/\s+/', $line, 2);
                $tag = substr($parts[0], 1);
                $value = $parts[1] ?? '';
                
                if ($tag === 'doc') {
                    $result['description'] = $value;
                } else {
                    $result['tags'][$tag] = $value;
                }
            } elseif ($currentTag) {
                $result['tags'][$currentTag] .= ' ' . $line;
            } else {
                $result['description'] .= ' ' . $line;
            }
        }

        return $result;
    }
}

class MetaCommentParser extends DocParser {
    public function supports(string $filePath): bool {
        return in_array(pathinfo($filePath, PATHINFO_EXTENSION), ['php', 'js']);
    }

    public function parse(string $content): array {
        $docs = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            if (preg_match('/\/\/\s*@(\w+):(.+)/', $line, $matches)) {
                $docs[$matches[1]] = trim($matches[2]);
            } elseif (preg_match('/\/\*\s*@(\w+):(.+?)\*\//', $line, $matches)) {
                $docs[$matches[1]] = trim($matches[2]);
            }
        }
        
        return $docs;
    }
}

class MarkdownParser extends DocParser {
    public function supports(string $filePath): bool {
        return pathinfo($filePath, PATHINFO_EXTENSION) === 'md';
    }

    public function parse(string $content): array {
        $result = [];
        
        // Parse frontmatter if exists
        if (preg_match('/^---\n(.+?)\n---\n/s', $content, $matches)) {
            $frontmatter = explode("\n", $matches[1]);
            foreach ($frontmatter as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $result[trim($key)] = trim($value);
                }
            }
            $content = substr($content, strlen($matches[0]));
        }
        
        $result['content'] = $content;
        return $result;
    }
}
