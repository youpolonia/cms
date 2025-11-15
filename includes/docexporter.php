<?php
/**
 * Base documentation exporter
 */
abstract class DocExporter {
    abstract public function export(array $docs, string $outputDir): void;
}

class MarkdownExporter extends DocExporter {
    public function export(array $docs, string $outputDir): void {
        foreach ($docs as $file => $data) {
            $outputFile = $this->getOutputPath($file, $outputDir, 'md');
            $content = $this->generateMarkdown($data);
            file_put_contents($outputFile, $content);
        }
    }

    private function getOutputPath(string $sourceFile, string $outputDir, string $ext): string {
        $relativePath = str_replace([DOCUMENT_ROOT, '\\'], ['', '/'], $sourceFile);
        $outputPath = $outputDir . dirname($relativePath);
        
        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }
        
        return $outputPath . '/' . basename($sourceFile, '.php') . '.' . $ext;
    }

    public function generateMarkdown(array $data): string {
        $output = "# Documentation\n\n";
        
        if (!empty($data['description'])) {
            $output .= "## Description\n" . $data['description'] . "\n\n";
        }

        if (!empty($data['tags'])) {
            $output .= "## Tags\n";
            foreach ($data['tags'] as $tag => $value) {
                $output .= "- `$tag`: $value\n";
            }
            $output .= "\n";
        }

        if (!empty($data['methods'])) {
            $output .= "## Methods\n";
            foreach ($data['methods'] as $method) {
                $output .= "### " . ($method['name'] ?? 'Unknown') . "\n";
                $output .= $method['description'] . "\n\n";
                
                if (!empty($method['tags'])) {
                    foreach ($method['tags'] as $tag => $value) {
                        $output .= "- **$tag**: $value\n";
                    }
                }
                $output .= "\n";
            }
        }

        return $output;
    }
}

class HtmlExporter extends DocExporter {
    public function export(array $docs, string $outputDir): void {
        require_once PathResolver::vendor('parsedown.php');
        $parsedown = new Parsedown();
        
        foreach ($docs as $file => $data) {
            $outputFile = $this->getOutputPath($file, $outputDir, 'html');
            $markdown = (new MarkdownExporter())->generateMarkdown($data);
            $html = $parsedown->text($markdown);
            
            $fullHtml = "
<!DOCTYPE html>
<html>
<head>
    <title>Documentation</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        code { background: #f5f5f5; padding: 2px 4px; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
$html
</body>
</html>";
            
            file_put_contents($outputFile, $fullHtml);
        }
    }
}

class PdfExporter extends DocExporter {
    public function export(array $docs, string $outputDir): void {
        require_once PathResolver::vendor('tcpdf/tcpdf.php');
        
        foreach ($docs as $file => $data) {
            $outputFile = $this->getOutputPath($file, $outputDir, 'pdf');
            $markdown = (new MarkdownExporter())->generateMarkdown($data);
            
            $pdf = new TCPDF();
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 12);
            $pdf->writeHTML($this->markdownToHtml($markdown));
            $pdf->Output($outputFile, 'F');
        }
    }
    
    private function markdownToHtml(string $markdown): string {
        // Simple markdown to HTML conversion for PDF
        $html = htmlspecialchars($markdown);
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/^# (.+)$/m', '
<h1>
$1</h1>', $html);
        $html = preg_replace('/^## (.+)$/m', '
<h2>
$1</h2>', $html);
        $html = preg_replace('/^### (.+)$/m', '
<h3>
$1</h3>', $html);
        $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);
        $html = nl2br($html);
        
        return $html;
    }
}
