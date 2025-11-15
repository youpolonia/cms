<?php

$router = new \App\Core\Router();

$router->group([
    'middleware' => ['auth:sanctum', 'verified']
], function() use ($router) {
    // Schedule CRUD operations
    $router->get('/schedules', 'ScheduleController@index')
        ->middleware('can:viewAny,App\Models\ScheduledEvent');
    $router->post('/schedules', 'ScheduleController@store')
        ->middleware('can:create,App\Models\ScheduledEvent');
    $router->get('/schedules/{schedule}', 'ScheduleController@show')
        ->middleware('can:view,schedule');
    $router->put('/schedules/{schedule}', 'ScheduleController@update')
        ->middleware('can:update,schedule');
    $router->delete('/schedules/{schedule}', 'ScheduleController@destroy')
        ->middleware('can:delete,schedule');

    // Special scheduling operations
    $router->get('/schedules/check-conflicts', 'ScheduleController@checkConflicts')
        ->middleware('can:viewAny,App\Models\ScheduledEvent');

    // Bulk operations
    $router->post('/schedules/bulk', 'ScheduleController@bulkStore')
        ->middleware('can:create,App\Models\ScheduledEvent');
    $router->put('/schedules/bulk', 'ScheduleController@bulkUpdate')
        ->middleware('can:update,App\Models\ScheduledEvent');
    $router->delete('/schedules/bulk', 'ScheduleController@bulkDestroy')
        ->middleware('can:delete,App\Models\ScheduledEvent');
});
