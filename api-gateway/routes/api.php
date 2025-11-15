<?php
declare(strict_types=1);

return [
    'v1' => [
        'content' => [
            'GET /pages' => 'ContentController@listPages',
            'GET /pages/{id}' => 'ContentController@getPage',
            'POST /pages' => 'ContentController@createPage'
        ],
        'federation' => [
            'POST /share' => 'FederationController@shareContent',
            'GET /sync' => 'FederationController@syncVersions',
            'POST /resolve' => 'FederationController@resolveConflicts',
            'POST /content' => 'FederationController@processContent'
        ],
        'users' => [
            'GET /me' => 'UserController@getCurrentUser',
            'PATCH /me' => 'UserController@updateCurrentUser'
        ],
        'admin' => [
            'GET /users' => 'AdminController@listUsers',
            'POST /users' => 'AdminController@createUser'
        ]
    ],
    'v2' => [
        // Future version routes will be added here
    ]
];
