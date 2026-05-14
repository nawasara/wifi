<?php

return [
    // Jumlah titik WiFi per halaman di tabel.
    'per_page' => 25,

    // Koordinat default peta (dipakai map view nanti) — pusat wilayah
    // Ponorogo. Disesuaikan kalau deployment-nya wilayah lain.
    'map' => [
        'default_lat' => env('WIFI_MAP_DEFAULT_LAT', -7.8696),
        'default_lng' => env('WIFI_MAP_DEFAULT_LNG', 111.4625),
        'default_zoom' => env('WIFI_MAP_DEFAULT_ZOOM', 13),
    ],
];
