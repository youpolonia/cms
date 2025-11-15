<?php

// Version analytics routes
add_route('POST', '/versions/{id}/analytics', 'AnalyticsController@trackEvent');
add_route('GET', '/versions/{id}/analytics', 'AnalyticsController@getAnalytics');
