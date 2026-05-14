<?php

namespace Nawasara\Wifi\Livewire\Point;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Nawasara\Wifi\Models\WifiPoint;

/**
 * CRUD titik WiFi publik.
 *
 * Status koneksi di-set manual di sini (v0.1.0 — belum ada probe). Data
 * koordinat dikumpulkan supaya nanti bisa di-plot di peta bersama CCTV.
 */
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    /** Filter status: '' = semua, 'connected', 'disconnected'. */
    #[Url(except: '')]
    public string $statusFilter = '';

    // -- Form modal state ------------------------------------------------------
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $location = '';
    public string $latitude = '';
    public string $longitude = '';
    public string $status = WifiPoint::STATUS_DISCONNECTED;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:connected,disconnected'],
            'is_active' => ['boolean'],
        ];
    }

    #[Computed]
    public function points()
    {
        return WifiPoint::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('location', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('name')
            ->paginate((int) config('nawasara-wifi.per_page', 25));
    }

    /**
     * Ringkasan jumlah per status — untuk badge counter di header.
     *
     * @return array{total:int, connected:int, disconnected:int}
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'total' => WifiPoint::count(),
            'connected' => WifiPoint::where('status', WifiPoint::STATUS_CONNECTED)->count(),
            'disconnected' => WifiPoint::where('status', WifiPoint::STATUS_DISCONNECTED)->count(),
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('wifi.point.create');
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        Gate::authorize('wifi.point.update');

        $point = WifiPoint::findOrFail($id);

        $this->editingId = $point->id;
        $this->name = $point->name;
        $this->location = (string) $point->location;
        $this->latitude = (string) ($point->latitude ?? '');
        $this->longitude = (string) ($point->longitude ?? '');
        $this->status = $point->status;
        $this->is_active = $point->is_active;

        $this->showForm = true;
    }

    public function save(): void
    {
        Gate::authorize($this->editingId ? 'wifi.point.update' : 'wifi.point.create');

        $validated = $this->validate();

        $lat = $validated['latitude'] !== '' ? $validated['latitude'] : null;
        $lng = $validated['longitude'] !== '' ? $validated['longitude'] : null;

        if ($this->editingId) {
            $point = WifiPoint::findOrFail($this->editingId);
            $point->fill([
                'name' => $validated['name'],
                'location' => $validated['location'] ?: null,
                'latitude' => $lat,
                'longitude' => $lng,
                'is_active' => $validated['is_active'],
            ]);
            // Status lewat setStatus() supaya status_changed_at konsisten
            // ter-update hanya saat benar-benar berubah.
            $point->setStatus($validated['status']);
            $point->save();
        } else {
            $point = new WifiPoint([
                'name' => $validated['name'],
                'location' => $validated['location'] ?: null,
                'latitude' => $lat,
                'longitude' => $lng,
                'is_active' => $validated['is_active'],
            ]);
            // Titik baru: set status awal + stempel waktu sejak awal.
            $point->status = $validated['status'];
            $point->status_changed_at = now();
            $point->save();
        }

        $this->showForm = false;
        $this->resetForm();
        unset($this->points, $this->stats);

        $this->dispatch('toast', type: 'success', message: 'Titik WiFi tersimpan.');
    }

    /**
     * Toggle cepat status dari tabel — tanpa buka modal. Praktis untuk
     * operator yang cuma mau tandai terhubung/putus.
     */
    public function toggleStatus(int $id): void
    {
        Gate::authorize('wifi.point.update');

        $point = WifiPoint::findOrFail($id);
        $point->setStatus(
            $point->isConnected()
                ? WifiPoint::STATUS_DISCONNECTED
                : WifiPoint::STATUS_CONNECTED
        );
        $point->save();

        unset($this->points, $this->stats);
        $this->dispatch('toast', type: 'success', message: 'Status diperbarui.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('wifi.point.delete');

        WifiPoint::findOrFail($id)->delete();

        unset($this->points, $this->stats);
        $this->dispatch('toast', type: 'success', message: 'Titik WiFi dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'location', 'latitude', 'longitude',
        ]);
        $this->status = WifiPoint::STATUS_DISCONNECTED;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('nawasara-wifi::livewire.pages.point.index')
            ->layout('nawasara-ui::components.layouts.app');
    }
}
