<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Wifi\Http\Api\PointController;

/*
|--------------------------------------------------------------------------
| WiFi API routes
|--------------------------------------------------------------------------
| Di-mount oleh WifiServiceProvider di prefix /api/v1/wifi dengan
| middleware group: api + api.auth + api.log.
*/

Route::middleware('scope:wifi.point.read')->group(function () {
    Route::get('/points', [PointController::class, 'index'])->name('wifi.points.index');
    Route::get('/points/{id}', [PointController::class, 'show'])
        ->whereNumber('id')
        ->name('wifi.points.show');
});
