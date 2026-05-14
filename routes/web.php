<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Wifi\Livewire\Point\Index as PointIndex;
use Spatie\Permission\Middleware\PermissionMiddleware;

Route::middleware(['web', 'auth'])->prefix('nawasara-wifi')->group(function () {
    Route::get('points', PointIndex::class)
        ->middleware(PermissionMiddleware::using('wifi.point.view'))
        ->name('nawasara-wifi.point.index');
});
