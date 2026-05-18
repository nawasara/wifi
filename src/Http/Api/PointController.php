<?php

namespace Nawasara\Wifi\Http\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nawasara\Wifi\Http\Resources\WifiPointResource;
use Nawasara\Wifi\Models\WifiPoint;

/**
 * Public API untuk WiFi point.
 *
 * Auth + scope dicek di middleware:
 *   - api.auth → token valid
 *   - scope:wifi.point.read → list + show
 */
class PointController extends Controller
{
    /**
     * GET /api/v1/wifi/points
     * Scope: wifi.point.read
     */
    public function index(Request $request): JsonResponse
    {
        $query = WifiPoint::query()
            ->where('is_active', true)
            ->orderBy('name');

        // Opsi: ?mappable=1 → hanya yang punya koordinat.
        if ($request->boolean('mappable')) {
            $query->whereNotNull('latitude')->whereNotNull('longitude');
        }

        $points = $query->get();

        return response()->json([
            'data' => WifiPointResource::collection($points)->resolve(),
            'meta' => ['total' => $points->count()],
        ]);
    }

    /**
     * GET /api/v1/wifi/points/{id}
     * Scope: wifi.point.read
     */
    public function show(int $id): JsonResponse
    {
        $point = WifiPoint::where('is_active', true)->findOrFail($id);

        return response()->json([
            'data' => (new WifiPointResource($point))->resolve(request()),
        ]);
    }
}
