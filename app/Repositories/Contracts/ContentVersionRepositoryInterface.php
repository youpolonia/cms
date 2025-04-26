<?php

namespace App\Repositories\Contracts;

use App\Models\ContentVersion;

interface ContentVersionRepositoryInterface
{
    public function findById(int $id): ?ContentVersion;
    public function findByContentId(int $contentId): array;
    public function create(array $data): ContentVersion;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function publish(int $id): bool;
    public function restore(int $id): bool;
    public function compareVersions(int $versionA, int $versionB): array;
}