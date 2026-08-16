<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Wifi\Http\Api\CitizenPointController;

/*
|--------------------------------------------------------------------------
| WiFi — rute aplikasi warga
|--------------------------------------------------------------------------
| Di-mount WifiServiceProvider di prefix /api/v1/citizen/wifi dengan
| middleware: api + api.citizen (JWT realm warga) + throttle.
|
| Seluruhnya baca saja. Tabelnya memang registry titik WiFi publik, jadi
| tidak ada penyaringan selain `is_active`.
*/

Route::get('/points', [CitizenPointController::class, 'index'])
    ->name('points.index');

Route::get('/points/{id}', [CitizenPointController::class, 'show'])
    ->whereNumber('id')
    ->name('points.show');
