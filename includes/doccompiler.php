<?php
/**
 * Documentation Compiler Core
 * Scans source files and generates documentation
 */
class DocCompiler {
    const MAX_SCAN_DEPTH = 10;
    const OUTPUT_DIR = 'docs/generated/';

    private $parsers = [];
    private $exporters = [];

    public function __construct() {
        $this->initParsers();
        $this->initExporters();
    }

    private function initParsers(): void {
        $this->parsers = [
            new PhpDocParser(),
            new MetaCommentParser(),
            new MarkdownParser()
        ];
    }

    private function initExporters(): void {
        $this->exporters = [
            'md' => new MarkdownExporter(),
            'html' => new HtmlExporter(),
            'pdf' => new PdfExporter()
        ];
    }

    public function scanSources(array $paths): array {
        $results = [];
        foreach ($paths as $path) {
            $results = array_merge(
                $results,
                $this->scanDirectory($path, 0)
            );
        }
        return $results;
    }

    private function scanDirectory(string $path, int $depth): array {
        if ($depth > self::MAX_SCAN_DEPTH) {
            return [];
        }

        $files = [];
        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $path . '/' . $item;
            if (is_dir($fullPath)) {
                $files = array_merge(
                    $files,
                    $this->scanDirectory($fullPath, $depth + 1)
                );
            } else {
                $files[] = $fullPath;
            }
        }
        
        return $files;
    }

    public function generateDocs(array $files): array {
        $docs = [];
        foreach ($files as $file) {
            $content = file_get_contents($file);
            foreach ($this->parsers as $parser) {
                if ($parser->supports($file)) {
                    $docs[$file] = $parser->parse($content);
                    break;
                }
            }
        }
        return $docs;
    }

    public function export(array $docs, string $format = 'md'): void {
        if (!isset($this->exporters[$format])) {
            throw new InvalidArgumentException("Unsupported format: $format");
        }

        if (!is_dir(self::OUTPUT_DIR)) {
            mkdir(self::OUTPUT_DIR, 0755, true);
        }

        $this->exporters[$format]->export($docs, self::OUTPUT_DIR);
    }
}
