<?php
declare(strict_types=1);
/**
 * AI Designer - Base Personality Interface
 * 
 * Defines contract for design personality classes.
 * Each personality provides specific design guidance for AI prompts.
 *
 * @package AiDesigner
 * @version 4.0
 */

namespace Core\AiDesigner\Personalities;

interface PersonalityInterface
{
    public function getName(): string;
    public function getKey(): string;
    public function getTraits(): string;
    public function getInfluences(): string;
    public function getColorGuidance(): string;
    public function getTypographyGuidance(): string;
    public function getLayoutGuidance(): string;
    public function getImageryGuidance(): string;
    public function getPromptContext(): string;
    public function toArray(): array;
}
