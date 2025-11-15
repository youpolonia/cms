<?php
declare(strict_types=1);

interface AIIntegrationProvider {
    public function process(array $input): array;
}
