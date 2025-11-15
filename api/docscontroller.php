<?php
declare(strict_types=1);

namespace Api;

use Services\DocGenerator;
use Modules\DocCompiler\DocCompiler;

class DocsController {
    private DocGenerator $docGenerator;

    public function __construct(DocGenerator $docGenerator) {
        $this->docGenerator = $docGenerator;
    }

    public function listVersions(): array {
        return $this->docGenerator->listVersions();
    }

    public function getVersion(string $version): ?string {
        return $this->docGenerator->getVersion($version);
    }

    public function generateDocs(array $endpoints): bool {
        return $this->docGenerator->generateApiDocs($endpoints);
    }

    public function compileDocs(string $content, string $format = 'html'): string {
        return DocCompiler::generateDocs($content, $format);
    }
}
