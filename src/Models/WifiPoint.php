<?php

namespace Nawasara\Wifi\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Titik WiFi publik.
 *
 * Status koneksi ('connected' | 'disconnected') di v0.1.0 di-set manual
 * via CRUD. setStatus() dipusatkan di sini supaya status_changed_at selalu
 * konsisten ter-update — dan jadi satu titik ubah kalau nanti ada probe.
 */
class WifiPoint extends Model
{
    use LogsActivity;

    public const STATUS_CONNECTED = 'connected';
    public const STATUS_DISCONNECTED = 'disconnected';

    protected $table = 'nawasara_wifi_points';

    protected $fillable = [
        'name',
        'location',
        'latitude',
        'longitude',
        'status',
        'status_changed_at',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
        'status_changed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'location',
                'latitude',
                'longitude',
                'status',
                'is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Ubah status koneksi + stempel status_changed_at. Tidak melakukan apa-apa
     * kalau status sama (hindari log/timestamp palsu). Tidak auto-save —
     * caller yang panggil ->save() supaya bisa di-batch.
     */
    public function setStatus(string $status): void
    {
        if (! in_array($status, [self::STATUS_CONNECTED, self::STATUS_DISCONNECTED], true)) {
            throw new \InvalidArgumentException("Status WiFi tidak valid: {$status}");
        }

        if ($this->status === $status) {
            return;
        }

        $this->status = $status;
        $this->status_changed_at = now();
    }

    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }

    /**
     * Punya koordinat lengkap? Map view hanya plot titik yang punya lat+lng.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Scope: titik yang bisa di-plot di peta (aktif + punya koordinat).
     */
    public function scopeMappable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }
}
