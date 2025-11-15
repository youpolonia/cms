<?php

/**
 * FieldRendererInterface defines the contract for field renderers
 */
interface FieldRendererInterface
{
    /**
     * Render a field with given value and context
     * 
     * @param array $field Field configuration
     * @param mixed $value Current field value
     * @param array $context Additional rendering context
     * @return string Rendered HTML
     */
    public function render(array $field, $value, array $context): string;
}
