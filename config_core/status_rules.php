<?php

return [
    // Global default rules
    '_default' => [
        1 => [2, 3], // Status 1 can transition to Status 2 or 3
        2 => [4],    // Status 2 can transition to Status 4
        3 => [4],    // Status 3 can transition to Status 4
        4 => [],     // Status 4 cannot transition to any other status
    ],
    
    // Tenant-specific overrides (keyed by tenant ID)
    'tenant_123' => [
        2 => [3, 4], // Example override for tenant_123
    ],
    
    'tenant_456' => [
        1 => [3], // Example override for tenant_456
    ]
];
