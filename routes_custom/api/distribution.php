<?php

$router = new \App\Core\Router();

$router->group([
    'prefix' => 'distribution',
    'middleware' => 'auth:sanctum'
], function() use ($router) {
    // Distribute content to channels
    $router->post('content/{content}/distribute', 'DistributionController@distributeContent');
    
    // Get available distribution channels
    $router->get('channels', 'DistributionController@getAvailableChannels');
    
    // Get channel status
    $router->get('channels/{channel}/status', 'DistributionController@getChannelStatus');
});
