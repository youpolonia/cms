<?php
/**
 * Content Version Routes - Framework-free implementation
 */

require_once __DIR__.'/../includes/routing/Router.php';

$router = new Router();

// Version history route
$router->get('/content/{content}/versions', 'ContentVersionHistoryController@index');

// Simple comparison route (redirects to comparison UI)
$router->get('/content/{content}/versions/compare', 'ContentVersionComparisonController@index');

// Version comparison routes
$router->get('/content/{content}/versions/compare', 'ContentVersionComparisonController@index');
$router->post('/content/{content}/versions/compare', 'ContentVersionComparisonController@compare');
$router->post('/content/{content}/versions/compare/chunked', 'ContentVersionComparisonController@compareChunked');
$router->get('/content/{content}/versions/compare/{diff}', 'ContentVersionComparisonController@show');
$router->delete('/content/{content}/versions/compare/{diff}', 'ContentVersionComparisonController@destroy');
$router->get('/content/{content}/versions/compare/analytics', 'ContentVersionComparisonController@analytics');

// Direct version comparison routes
$router->get('/content/{content}/versions/compare/{oldVersion}/{newVersion}', 'ContentVersionRestorationController@compare');
$router->get('/content/{content}/versions/{version}/compare', 'ContentVersionRestorationController@compare');

// Version resource routes
$router->get('/content/{content}/versions/{version}', 'ContentVersionRestorationController@show');
$router->put('/content/{content}/versions/{version}', 'ContentVersionRestorationController@update');
$router->delete('/content/{content}/versions/{version}', 'ContentVersionRestorationController@destroy');

// Rollback routes
$router->post('/content/{content}/versions/{version}/prepare-restore', 'ContentVersionRestorationController@prepareRestore');
$router->post('/content/{content}/versions/{version}/confirm-restore', 'ContentVersionRestorationController@confirmRestore');
$router->get('/content/{content}/versions/{version}/analytics', 'ContentVersionRestorationController@analytics');

// Branch management routes
$router->post('/content/{content}/versions/branches', 'ContentBranchingController@store');
$router->get('/content/{content}/versions/branches', 'ContentBranchingController@index');
$router->post('/content/{content}/versions/branches/{branch}/merge', 'ContentBranchingController@merge');