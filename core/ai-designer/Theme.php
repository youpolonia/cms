<?php
declare(strict_types=1);
/**
 * Theme Value Object
 * 
 * Holds all data for a generated theme.
 *
 * @package AiDesigner
 * @version 4.0
 */

namespace Core\AiDesigner;

class Theme
{
    private ?int $id = null;
    private string $slug;
    private string $path;
    private string $name;
    private array $data;
    private ?string $personality = null;
    private array $analysis = [];
    private array $designSystem = [];
    private array $pages = [];
    private ?string $header = null;
    private ?string $footer = null;
    private array $headerJson = [];
    private array $footerJson = [];
    private array $tbExport = [];

    public function __construct(array $data)
    {
        $this->slug = $data['slug'] ?? '';
        $this->path = $data['path'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->data = $data;
    }

    // ═══════════════════════════════════════════════════════════════
    // GETTERS
    // ═══════════════════════════════════════════════════════════════

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getPersonality(): ?string
    {
        return $this->personality;
    }

    public function getAnalysis(): array
    {
        return $this->analysis;
    }

    public function getDesignSystem(): array
    {
        return $this->designSystem;
    }

    public function getPages(): array
    {
        return $this->pages;
    }

    public function getPageNames(): array
    {
        return array_keys($this->pages);
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function getFooter(): ?string
    {
        return $this->footer;
    }

    public function getHeaderJson(): array
    {
        return $this->headerJson;
    }

    public function getFooterJson(): array
    {
        return $this->footerJson;
    }

    public function getTbExport(): array
    {
        return $this->tbExport;
    }

    public function getTbExportPath(): string
    {
        return $this->path . '/tb-export';
    }

    // ═══════════════════════════════════════════════════════════════
    // SETTERS
    // ═══════════════════════════════════════════════════════════════

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setPersonality(string $personality): void
    {
        $this->personality = $personality;
    }

    public function setAnalysis(array $analysis): void
    {
        $this->analysis = $analysis;
    }

    public function setDesignSystem(array $designSystem): void
    {
        $this->designSystem = $designSystem;
    }

    public function addPage(string $name, string $html): void
    {
        $this->pages[$name] = $html;
    }

    public function setHeader(string $html): void
    {
        $this->header = $html;
    }

    public function setFooter(string $html): void
    {
        $this->footer = $html;
    }

    public function setHeaderJson(array $json): void
    {
        $this->headerJson = $json;
    }

    public function setFooterJson(array $json): void
    {
        $this->footerJson = $json;
    }

    public function setTbExport(array $export): void
    {
        $this->tbExport = $export;
    }

    public function addPageTbExport(string $name, array $json): void
    {
        $this->tbExport['pages'][$name] = $json;
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get complete theme data as array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'path' => $this->path,
            'name' => $this->name,
            'personality' => $this->personality,
            'analysis' => $this->analysis,
            'design_system' => $this->designSystem,
            'pages' => $this->getPageNames(),
            'tb_export_path' => $this->getTbExportPath()
        ];
    }
}
