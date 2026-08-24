<?php

namespace Nawasara\Wifi\Http\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nawasara\Wifi\Http\Resources\CitizenWifiPointResource;
use Nawasara\Wifi\Models\WifiPoint;

/**
 * Titik WiFi untuk aplikasi warga.
 *
 * Berbeda dengan CCTV, tabel ini **memang** registry titik WiFi publik —
 * seluruh isinya sejak awal dimaksudkan untuk warga. Karena itu tidak ada
 * penanda `is_public` tersendiri: menambahkannya hanya akan menduplikasi arti
 * `is_active`, dan dua penanda yang berarti sama akan berbeda isi cepat atau
 * lambat.
 *
 * Tetap dipisah dari {@see PointController} agar khalayaknya jelas dan bentuk
 * jawabannya dapat berubah untuk salah satu tanpa merusak yang lain.
 */
class CitizenPointController extends Controller
{
    /**
     * GET /api/v1/citizen/wifi/points
     */
    public function index(Request $request): JsonResponse
    {
        $query = WifiPoint::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($request->boolean('mappable')) {
            $query->whereNotNull('latitude')->whereNotNull('longitude');
        }

        $points = $query->get();

        return response()->json([
            'data' => CitizenWifiPointResource::collection($points)->resolve(),

            // Ringkasan dihitung SERVER, bukan aplikasi.
            //
            // Aplikasi bisa saja menghitungnya sendiri dari daftar, tetapi
            // angka yang dihitung dua tempat berbeda akan berbeda suatu hari
            // — dan yang salah adalah yang dilihat warga. Menghitung di sini
            // juga berarti angkanya tetap benar bila kelak daftarnya
            // dipaginasi.
            'meta' => [
                'total' => $points->count(),
                'online' => $points->where('status', WifiPoint::STATUS_CONNECTED)->count(),

                // Berapa kecamatan tercakup. Dihitung dari `location` yang
                // BOLEH kosong — titik tanpa lokasi tidak ikut dihitung,
                // bukan dihitung sebagai kecamatan tanpa nama.
                'districts' => $points->pluck('location')
                    ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                    ->unique()
                    ->count(),
            ],
        ]);
    }

    /**
     * GET /api/v1/citizen/wifi/points/{id}
     */
    public function show(int $id): JsonResponse
    {
        $point = WifiPoint::where('is_active', true)->findOrFail($id);

        return response()->json([
            'data' => (new CitizenWifiPointResource($point))->resolve(request()),
        ]);
    }
}
