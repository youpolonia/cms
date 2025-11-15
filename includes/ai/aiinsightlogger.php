<?php
// Minimal AI insight logger shim
if (!function_exists('ai_insight_log')) {
    function ai_insight_log(string $event, array $meta = []): void { /* no-op in DEV */ }
}
