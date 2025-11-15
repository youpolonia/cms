<?php
declare(strict_types=1);

namespace CMS\Content;

/**
 * Manages taxonomies and their terms
 */
class TaxonomyManager
{
    private array $taxonomies = [];
    private array $terms = [];
    private array $contentTermMap = [];

    /**
     * Register a new taxonomy
     * @param string $taxonomyName Unique taxonomy identifier
     * @param array $definition Taxonomy configuration
     * @throws \InvalidArgumentException If taxonomy is invalid
     */
    public function registerTaxonomy(string $taxonomyName, array $definition): void
    {
        $this->validateTaxonomyDefinition($definition);
        
        if (isset($this->taxonomies[$taxonomyName])) {
            throw new \InvalidArgumentException("Taxonomy '$taxonomyName' already exists");
        }

        $this->taxonomies[$taxonomyName] = $definition;
    }

    /**
     * Add a term to a taxonomy
     * @param string $taxonomyName Taxonomy identifier
     * @param string $termName Term name
     * @param int|null $parentId Parent term ID (null for root terms)
     * @return int The new term ID
     * @throws \InvalidArgumentException If taxonomy doesn't exist
     */
    public function addTerm(string $taxonomyName, string $termName, ?int $parentId = null): int
    {
        if (!isset($this->taxonomies[$taxonomyName])) {
            throw new \InvalidArgumentException("Taxonomy '$taxonomyName' not found");
        }

        $termId = count($this->terms) + 1;
        $this->terms[$termId] = [
            'id' => $termId,
            'taxonomy' => $taxonomyName,
            'name' => $termName,
            'parent_id' => $parentId
        ];

        return $termId;
    }

    /**
     * Associate a term with content
     * @param int $contentId Content ID
     * @param int $termId Term ID
     * @throws \InvalidArgumentException If term doesn't exist
     */
    public function associateTerm(int $contentId, int $termId): void
    {
        if (!isset($this->terms[$termId])) {
            throw new \InvalidArgumentException("Term ID $termId not found");
        }

        $this->contentTermMap[$contentId][] = $termId;
    }

    /**
     * Get terms for specific content
     * @param int $contentId Content ID
     * @return array Array of term IDs
     */
    public function getContentTerms(int $contentId): array
    {
        return $this->contentTermMap[$contentId] ?? [];
    }

    /**
     * Validate taxonomy definition structure
     * @param array $definition Taxonomy definition to validate
     * @throws \InvalidArgumentException If definition is invalid
     */
    private function validateTaxonomyDefinition(array $definition): void
    {
        $requiredFields = ['label', 'hierarchical'];
        
        foreach ($requiredFields as $field) {
            if (!isset($definition[$field])) {
                throw new \InvalidArgumentException("Missing required field '$field' in taxonomy definition");
            }
        }

        if (!is_bool($definition['hierarchical'])) {
            throw new \InvalidArgumentException("'hierarchical' must be a boolean");
        }
    }
}
