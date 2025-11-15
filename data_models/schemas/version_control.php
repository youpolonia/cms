<?php
// database/schemas/version_control.php
return [
    'tables' => [
        'content_versions' => [
            'columns' => [
                'id' => 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
                'content_id' => 'BIGINT UNSIGNED NOT NULL',
                'version_number' => 'INT UNSIGNED NOT NULL',
                'content_data' => 'JSON NOT NULL',
                'version_hash' => 'CHAR(64) NOT NULL COMMENT "SHA-256 hash of content_data"',
                'is_current' => 'BOOLEAN NOT NULL DEFAULT FALSE',
                'status' => "ENUM('draft','pending_review','approved','published','archived') NOT NULL DEFAULT 'draft'",
                'change_reason' => 'TEXT',
                'created_by' => 'BIGINT UNSIGNED NOT NULL',
                'tenant_id' => 'CHAR(36) NOT NULL',
                'created_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
            ],
            'indexes' => [
                'idx_content_versions_content' => ['content_id'],
                'idx_content_versions_tenant' => ['tenant_id'],
                'idx_content_versions_status' => ['status'],
                'idx_content_versions_current' => ['is_current'],
                'unq_content_versions_version' => ['content_id', 'version_number', 'tenant_id']
            ],
            'constraints' => [
                'fk_content_versions_content' => [
                    'columns' => 'content_id',
                    'references' => 'contents(id)',
                    'on_delete' => 'CASCADE'
                ],
                'fk_content_versions_creator' => [
                    'columns' => 'created_by',
                    'references' => 'users(id)',
                    'on_delete' => 'RESTRICT'
                ]
            ]
        ],
        'version_metadata' => [
            'columns' => [
                'id' => 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
                'version_id' => 'BIGINT UNSIGNED NOT NULL',
                'meta_key' => 'VARCHAR(64) NOT NULL',
                'meta_value' => 'TEXT',
                'tenant_id' => 'CHAR(36) NOT NULL'
            ],
            'indexes' => [
                'idx_version_metadata_version' => ['version_id'],
                'idx_version_metadata_key' => ['meta_key'],
                'idx_version_metadata_tenant' => ['tenant_id'],
                'unq_version_metadata_entry' => ['version_id', 'meta_key', 'tenant_id']
            ],
            'constraints' => [
                'fk_version_metadata_version' => [
                    'columns' => 'version_id',
                    'references' => 'content_versions(id)',
                    'on_delete' => 'CASCADE'
                ]
            ]
        ]
    ],
    'relationships' => [
        'content_versions' => [
            'has_many' => ['version_metadata'],
            'belongs_to' => ['contents', 'users']
        ]
    ]
];
