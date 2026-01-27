<?php
declare(strict_types=1);

namespace Services;

class DocGenerator {
    private string $storagePath;
    private array $versionMap = [];

    public function __construct(string $storagePath) {
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        $this->storagePath = $storagePath;
        $this->loadVersionMap();
    }

    public function generateApiDocs(array $endpoints): bool {
        $markdown = "# API Documentation\n\n";
        foreach ($endpoints as $endpoint) {
            $markdown .= "## {$endpoint['method']} {$endpoint['path']}\n";
            $markdown .= "{$endpoint['description']}\n\n";
            if (!empty($endpoint['params'])) {
                $markdown .= "### Parameters\n";
                foreach ($endpoint['params'] as $param) {
                    $markdown .= "- `{$param['name']}` ({$param['type']}): {$param['description']}\n";
                }
                $markdown .= "\n";
            }
        }

        $version = date('YmdHis');
        return $this->storeVersion($version, $markdown);
    }

    public function storeVersion(string $version, string $content): bool {
        $filename = "{$this->storagePath}/docs_{$version}.md";
        $result = file_put_contents($filename, $content);
        if ($result !== false) {
            $this->versionMap[$version] = $filename;
            $this->saveVersionMap();
            return true;
        }
        return false;
    }

    public function getVersion(string $version): ?string {
        if (isset($this->versionMap[$version])) {
            return file_get_contents($this->versionMap[$version]) ?: null;
        }
        return null;
    }

    public function listVersions(): array {
        return array_keys($this->versionMap);
    }

    private function loadVersionMap(): void {
        $mapFile = "{$this->storagePath}/versions.json";
        if (file_exists($mapFile)) {
            $this->versionMap = json_decode(file_get_contents($mapFile), true) ?: [];
        }
    }

    private function saveVersionMap(): void {
        $mapFile = "{$this->storagePath}/versions.json";
        file_put_contents($mapFile, json_encode($this->versionMap));
    }
}
