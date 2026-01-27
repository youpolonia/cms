<?php

return [
    'git' => [
        'repository_path' => storage_path('tests/repos/test-repo'),
        'author_name' => 'Test User',
        'author_email' => 'test@example.com',
        'branch_prefix' => 'test-branch-',
        'tag_prefix' => 'test-tag-',
        'commit_message_template' => 'Test commit: %s'
    ]
];
