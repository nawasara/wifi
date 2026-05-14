# nawasara/wifi

Monitoring titik WiFi publik untuk framework superapp Nawasara. Registry
hotspot dengan koordinat lokasi dan status koneksi — disiapkan untuk
ditampilkan di peta bersama titik CCTV (`nawasara/cctv`).

## Status v0.1.0

| Fitur | Status |
|---|---|
| Registry titik WiFi + CRUD | ✅ siap |
| Koordinat (latitude/longitude) per titik | ✅ siap |
| Status koneksi (terhubung / tidak terhubung) — **manual** | ✅ siap |
| Toggle status cepat dari tabel | ✅ siap |
| Auto-probe status (ping/HTTP) | ⏳ menyusul |
| Map view (gabung dengan CCTV) | ⏳ menyusul — dikerjakan terpisah |

Status koneksi di v0.1.0 di-set **manual** lewat CRUD (admin toggle).
Belum ada probe otomatis — itu butuh kolom IP/host + logika probe, bisa
ditambah nanti tanpa migrasi besar (struktur `status` + `status_changed_at`
sudah disiapkan untuk itu).

## Setup

```bash
php artisan migrate
php artisan db:seed --class="Nawasara\\Wifi\\Database\\Seeders\\PermissionSeeder"
```

## Model

`WifiPoint` (`nawasara_wifi_points`):
- `name`, `location` — identitas titik
- `latitude`, `longitude` — koordinat, `decimal(10,7)`, nullable. Hanya
  titik dengan koordinat lengkap yang di-plot di peta (scope `mappable()`).
- `status` — `connected` | `disconnected`. Ubah lewat `setStatus()` supaya
  `status_changed_at` konsisten ter-stempel.
- `is_active` — admin enable/disable.

## Permissions

| Permission | Untuk |
|---|---|
| `wifi.point.view` | Lihat daftar titik WiFi |
| `wifi.point.create` | Tambah titik |
| `wifi.point.update` | Edit titik + toggle status |
| `wifi.point.delete` | Hapus titik |

## Roadmap

- **Auto-probe**: tambah kolom `ip_address`/`host` + command `wifi:probe`
  (pola seperti `cctv:probe`) untuk update status otomatis.
- **Map view**: halaman peta interaktif yang plot marker WiFi + CCTV
  (`nawasara/cctv` juga sudah punya koordinat) di satu peta.
