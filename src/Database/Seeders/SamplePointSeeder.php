<?php

namespace Nawasara\Wifi\Database\Seeders;

use Illuminate\Database\Seeder;
use Nawasara\Wifi\Models\WifiPoint;

/**
 * Seed beberapa titik WiFi sample (Ponorogo).
 *
 * Bukan auto-seed. Pakai eksplisit:
 *
 *   php artisan db:seed --class="Nawasara\\Wifi\\Database\\Seeders\\SamplePointSeeder"
 *
 * Idempotent: firstOrCreate by name. Status default disconnected —
 * operator toggle manual lewat UI setelah cek koneksi fisik.
 */
class SamplePointSeeder extends Seeder
{
    private const POINTS = [
        ['name' => 'WiFi Alun-Alun', 'location' => 'Alun-Alun Ponorogo', 'lat' => '-7.8696', 'lng' => '111.4625'],
        ['name' => 'WiFi Stasiun',   'location' => 'Stasiun Ponorogo',   'lat' => '-7.8721', 'lng' => '111.4543'],
    ];

    public function run(): void
    {
        $created = 0;

        foreach (self::POINTS as $data) {
            $point = WifiPoint::firstOrNew(['name' => $data['name']]);

            $isNew = ! $point->exists;

            $point->fill([
                'location' => $data['location'],
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
                'is_active' => true,
            ]);

            // Hanya titik baru yang stempel status awal — jangan timpa
            // toggle manual operator di titik yang sudah ada.
            if ($isNew) {
                $point->status = WifiPoint::STATUS_DISCONNECTED;
                $point->status_changed_at = now();
            }

            $point->save();
            $created++;
        }

        $this->command?->info("Seeded {$created} titik WiFi sample.");
    }
}
