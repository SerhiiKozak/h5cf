<?php

use App\Http\Controllers\Api\V1\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'request.log',
    'owner.header',
    'throttle:60,1',
])->prefix('v1')->group(function () {
    Route::get('/health', HealthCheckController::class);
});
