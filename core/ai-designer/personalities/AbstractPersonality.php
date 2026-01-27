<?php
declare(strict_types=1);
/**
 * AI Designer - Abstract Personality Base
 * 
 * @package AiDesigner
 * @version 4.0
 */

namespace Core\AiDesigner\Personalities;

require_once __DIR__ . '/PersonalityInterface.php';

abstract class AbstractPersonality implements PersonalityInterface
{
    protected string $name;
    protected string $key;
    protected string $traits;
    protected string $influences;
    protected string $colorGuidance;
    protected string $typographyGuidance;
    protected string $layoutGuidance;
    protected string $imageryGuidance;

    public function getName(): string { return $this->name; }
    public function getKey(): string { return $this->key; }
    public function getTraits(): string { return $this->traits; }
    public function getInfluences(): string { return $this->influences; }
    public function getColorGuidance(): string { return $this->colorGuidance; }
    public function getTypographyGuidance(): string { return $this->typographyGuidance; }
    public function getLayoutGuidance(): string { return $this->layoutGuidance; }
    public function getImageryGuidance(): string { return $this->imageryGuidance; }

    public function getPromptContext(): string
    {
        return <<<CONTEXT
DESIGN PERSONALITY: {$this->name}

DESIGN TRAITS:
{$this->traits}

INFLUENCES:
{$this->influences}

COLOR GUIDANCE:
{$this->colorGuidance}

TYPOGRAPHY GUIDANCE:
{$this->typographyGuidance}

LAYOUT GUIDANCE:
{$this->layoutGuidance}

IMAGERY GUIDANCE:
{$this->imageryGuidance}
CONTEXT;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'key' => $this->key,
            'traits' => $this->traits,
            'influences' => $this->influences,
            'color_guidance' => $this->colorGuidance,
            'typography_guidance' => $this->typographyGuidance,
            'layout_guidance' => $this->layoutGuidance,
            'imagery_guidance' => $this->imageryGuidance
        ];
    }
}
