<?php

namespace Nawasara\Wifi\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Nawasara\Wifi\Models\WifiPoint;

/**
 * Transformer titik WiFi untuk public API. Eksplisit field list.
 *
 * @mixin WifiPoint
 */
class WifiPointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'status' => $this->status,
            'status_changed_at' => $this->status_changed_at?->toIso8601String(),
        ];
    }
}
