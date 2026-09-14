# nawasara/wifi

Public WiFi point monitoring for the Nawasara superapp framework. It is a hotspot registry with location coordinates and connection status, meant to be shown on a map alongside CCTV points (`nawasara/cctv`).

## Status v0.1.0

| Feature | Status |
|---|---|
| WiFi point registry plus CRUD | ready |
| Coordinates (latitude/longitude) per point | ready |
| Connection status (connected / disconnected), **manual** | ready |
| Quick status toggle from the table | ready |
| Automatic status probe (ping/HTTP) | not built yet |
| Map view (combined with CCTV) | not built yet, handled separately |

Connection status in v0.1.0 is set **manually** through CRUD (an admin toggle). There is no automatic probe yet. That would need an IP/host column plus probe logic, which can be added later without a large migration (the `status` and `status_changed_at` structure is already in place for it).

## Setup

```bash
php artisan migrate
php artisan db:seed --class="Nawasara\\Wifi\\Database\\Seeders\\PermissionSeeder"
```

## Model

`WifiPoint` (`nawasara_wifi_points`):
- `name`, `location`: point identity
- `latitude`, `longitude`: coordinates, `decimal(10,7)`, nullable. Only points with complete coordinates are plotted on the map (the `mappable()` scope).
- `status`: `connected` or `disconnected`. Change it through `setStatus()` so `status_changed_at` is stamped consistently.
- `is_active`: admin enable/disable.

## Permissions

| Permission | For |
|---|---|
| `wifi.point.view` | View the WiFi point list |
| `wifi.point.create` | Add a point |
| `wifi.point.update` | Edit a point and toggle status |
| `wifi.point.delete` | Delete a point |

## Roadmap

- **Auto-probe**: add an `ip_address`/`host` column plus a `wifi:probe` command (following the `cctv:probe` pattern) to update status automatically.
- **Map view**: an interactive map page that plots WiFi and CCTV markers (`nawasara/cctv` also already has coordinates) on a single map.
