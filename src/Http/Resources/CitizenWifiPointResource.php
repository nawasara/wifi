<?php

namespace Nawasara\Wifi\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Nawasara\Wifi\Models\WifiPoint;

/**
 * Titik WiFi sebagaimana dilihat warga.
 *
 * DAFTAR-IZIN, sama alasannya dengan {@see CitizenWifiPointResource} pada
 * CCTV: kolom baru pada tabel tidak boleh ikut keluar hanya karena tidak ada
 * yang ingat membuangnya.
 *
 * `status_changed_at` sengaja tidak dikirim. Bagi warga yang perlu tahu hanya
 * titiknya sedang hidup atau tidak; kapan tepatnya status berubah adalah
 * keterangan operasional untuk panel admin.
 *
 * @mixin WifiPoint
 */
class CitizenWifiPointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,

            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,

            // 'connected' | 'disconnected'. Aplikasi wajib punya cadangan
            // untuk nilai tak dikenal, karena kelak dapat bertambah.
            'status' => $this->status,
        ];
    }
}
