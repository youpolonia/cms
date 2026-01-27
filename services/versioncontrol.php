<?php
declare(strict_types=1);

namespace Services;

class VersionControl {
    private DocGenerator $docGenerator;
    private array $branches = [];
    private string $currentBranch = 'main';

    public function __construct(DocGenerator $docGenerator) {
        $this->docGenerator = $docGenerator;
        $this->branches['main'] = [];
    }

    public function createBranch(string $name, string $fromBranch = 'main'): bool {
        if (isset($this->branches[$name])) {
            return false;
        }

        $this->branches[$name] = $this->branches[$fromBranch] ?? [];
        return true;
    }

    public function switchBranch(string $name): bool {
        if (!isset($this->branches[$name])) {
            return false;
        }
        $this->currentBranch = $name;
        return true;
    }

    public function getCurrentBranch(): string {
        return $this->currentBranch;
    }

    public function listBranches(): array {
        return array_keys($this->branches);
    }

    public function mergeBranch(string $sourceBranch, string $targetBranch = 'main'): bool {
        if (!isset($this->branches[$sourceBranch]) || !isset($this->branches[$targetBranch])) {
            return false;
        }

        $this->branches[$targetBranch] = array_merge(
            $this->branches[$targetBranch],
            $this->branches[$sourceBranch]
        );
        return true;
    }

    public function approveVersion(string $version, string $branch = 'main'): bool {
        if (!isset($this->branches[$branch])) {
            return false;
        }

        $this->branches[$branch][] = $version;
        return true;
    }
}
