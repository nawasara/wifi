<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registry titik WiFi publik — satu baris per hotspot.
 *
 * Tujuan: ditampilkan di peta (digabung dengan titik CCTV). Status
 * koneksi di v0.1.0 di-set MANUAL lewat CRUD (admin toggle) — belum ada
 * probe otomatis. Kolom & struktur disiapkan supaya auto-probe bisa
 * ditambah nanti tanpa migrasi ulang besar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nawasara_wifi_points', function (Blueprint $table) {
            $table->id();

            // Identitas
            $table->string('name');                  // "WiFi Alun-Alun", dll
            $table->string('location')->nullable();  // deskripsi lokasi fisik

            // Koordinat — untuk plot di peta. decimal(10,7) ~= presisi 1.1 cm.
            // nullable: titik bisa didaftarkan dulu sebelum koordinat pasti.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Status koneksi: 'connected' | 'disconnected'.
            // v0.1.0 manual; default disconnected supaya titik baru tidak
            // langsung tampil "hijau" sebelum dikonfirmasi.
            $table->string('status')->default('disconnected');

            // Kapan status terakhir berubah — berguna untuk "terhubung sejak"
            // / "putus sejak" di UI, dan jadi pijakan kalau nanti ada probe.
            $table->timestamp('status_changed_at')->nullable();

            $table->boolean('is_active')->default(true); // admin enable/disable

            $table->timestamps();

            $table->index('status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nawasara_wifi_points');
    }
};
