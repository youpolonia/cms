<?php

// Content management routes
add_route('GET', '/content', 'ContentController@index');
add_route('POST', '/content', 'ContentController@store', ['middleware' => ['CheckPermission:content_edit']]);
add_route('GET', '/content/{id}', 'ContentController@show');
add_route('PUT', '/content/{id}', 'ContentController@update', ['middleware' => ['CheckPermission:content_edit']]);
add_route('DELETE', '/content/{id}', 'ContentController@destroy', ['middleware' => ['CheckPermission:content_delete']]);
add_route('POST', '/content/bulk', 'ContentController@bulk', ['middleware' => ['CheckPermission:content_edit']]);
add_route('POST', '/content/{id}/rollback', 'ContentController@rollback', ['middleware' => ['CheckPermission:content_edit']]);
add_route('POST', '/content/schedule', 'ContentController@schedule', ['middleware' => ['CheckPermission:content_schedule']]);
add_route('POST', '/content/scheduled/{id}/publish', 'ContentController@publishScheduled', ['middleware' => ['CheckPermission:content_publish']]);

// Version scheduling routes
add_route('POST', '/content/{id}/versions/schedule', 'ContentController@scheduleVersion', ['middleware' => ['CheckPermission:content_schedule']]);
add_route('GET', '/content/{id}/versions/conflicts', 'ContentController@checkVersionConflicts', ['middleware' => ['CheckPermission:content_schedule']]);
add_route('POST', '/content/{id}/versions/resolve', 'ContentController@resolveVersionConflict', ['middleware' => ['CheckPermission:content_schedule']]);
