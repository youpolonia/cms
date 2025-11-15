<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GatewayController;

Route::any('/{service}/{path}', [GatewayController::class, 'route'])
    ->where('path', '.*');
