<?php

// AI-powered personalization routes
add_route('POST', '/personalization/recommendations', 'PersonalizationController@generateRecommendations', [
    'middleware' => ['auth', 'CheckPermission:content_personalize']
]);

add_route('GET', '/personalization/models', 'PersonalizationController@listModels', [
    'middleware' => ['auth', 'CheckPermission:content_personalize']
]);

add_route('POST', '/personalization/train', 'PersonalizationController@trainModel', [
    'middleware' => ['auth', 'CheckPermission:content_personalize']
]);

// Include this file in api/v1/routes.php
