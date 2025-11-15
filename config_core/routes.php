<?php
return [
    'GET' => [
        '/' => 'HomeController@index',
        '/login' => 'AuthController@showLogin',
        '/logout' => 'AuthController@logout',
        
        // Admin routes
        '/admin' => 'Admin\DashboardController@index',
        '/admin/content' => 'Admin\ContentController@index',
        '/admin/content/create' => 'Admin\ContentController@create',
        '/admin/content/edit/{id}' => 'Admin\ContentController@edit',
        
        // Admin user management
        '/admin/users' => 'Admin\UserController@index',
        '/admin/users/create' => 'Admin\UserController@create',
        '/admin/users/edit/{id}' => 'Admin\UserController@edit',
        
        // API routes
        '/api/content' => 'Api\ContentController@index',
        '/api/content/{id}' => 'Api\ContentController@show',
        
        // Error routes
        '/404' => 'ErrorController@notFound',
        '/500' => 'ErrorController@serverError',
        
        // Public content routes
        '/blog' => 'Api\ContentController@blogIndex',
        '/company/{slug}' => 'CompanyController@show',
        '/{slug}' => 'ContentController@show'
    ],
    'POST' => [
        '/login' => 'AuthController@handleLogin',
        '/admin/content/store' => 'Admin\ContentController@store',
        '/admin/content/update/{id}' => 'Admin\ContentController@update',
        '/admin/content/delete/{id}' => 'Admin\ContentController@delete',
        '/admin/users/store' => 'Admin\UserController@store',
        '/admin/users/update/{id}' => 'Admin\UserController@update',
        '/admin/users/delete/{id}' => 'Admin\UserController@delete',
        '/admin/custom-fields/save-assignments' => 'FieldAssignmentController@saveAssignments',
        '/api/content' => 'Api\ContentController@store'
    ]
];
