<?php

interface WidgetInterface
{
    /**
     * Render the widget with given context
     * @param array $context Key-value pairs for widget rendering
     * @return string Rendered HTML output
     */
    public function render(array $context = []): string;

    /**
     * Check if widget is active and should be rendered
     * @return bool Whether widget is active
     */
    public function isActive(): bool;

    /**
     * Check if widget should be rendered for current tenant
     * @param string $tenantId Current tenant identifier
     * @return bool Whether widget is available for tenant
     */
    public function isAvailableForTenant(string $tenantId): bool;
}
