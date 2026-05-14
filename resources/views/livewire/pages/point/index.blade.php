<div>
    <x-nawasara-ui::page.container>
        <x-nawasara-ui::page-header
            title="Titik WiFi"
            description="Registry hotspot WiFi publik. Data koordinat & status untuk ditampilkan di peta."
            :count="$this->stats['total'].' titik'">
            <x-nawasara-ui::button color="primary" wire:click="openCreate" permission="wifi.point.create">
                <x-slot:icon><x-lucide-plus class="size-4" /></x-slot:icon>
                Tambah Titik
            </x-nawasara-ui::button>
        </x-nawasara-ui::page-header>

        {{-- Ringkasan status --}}
        <div class="mb-4 flex flex-wrap gap-3">
            <x-nawasara-ui::stat-card label="Terhubung" :value="$this->stats['connected']"
                icon="lucide-wifi" color="success" />
            <x-nawasara-ui::stat-card label="Tidak Terhubung" :value="$this->stats['disconnected']"
                icon="lucide-wifi-off" color="danger" />
        </div>

        {{-- Toolbar — filter status + search --}}
        <x-nawasara-ui::filter-bar
            search-model="search"
            search-placeholder="Cari nama atau lokasi...">
            <x-nawasara-ui::filter-dropdown
                label="Status"
                model="statusFilter"
                :items="['connected' => 'Terhubung', 'disconnected' => 'Tidak Terhubung']" />
        </x-nawasara-ui::filter-bar>

        @if ($this->points->isEmpty())
            <x-nawasara-ui::empty-state icon="lucide-wifi-off" title="Belum ada titik WiFi"
                description="Tambahkan titik hotspot WiFi publik untuk mulai monitoring.">
                <x-nawasara-ui::button color="primary" wire:click="openCreate" permission="wifi.point.create">
                    <x-slot:icon><x-lucide-plus class="size-4" /></x-slot:icon>
                    Tambah Titik
                </x-nawasara-ui::button>
            </x-nawasara-ui::empty-state>
        @else
            <x-nawasara-ui::table
                :headers="['Nama', 'Lokasi', 'Koordinat', 'Status', 'Aksi']"
                stickyLast>
                <x-slot:table>
                    @foreach ($this->points as $point)
                        <tr wire:key="point-{{ $point->id }}">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-neutral-100">
                                <div class="flex items-center gap-2">
                                    {{ $point->name }}
                                    @unless ($point->is_active)
                                        <x-nawasara-ui::badge color="neutral">nonaktif</x-nawasara-ui::badge>
                                    @endunless
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                                {{ $point->location ?: '—' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-gray-600 dark:text-neutral-400">
                                @if ($point->hasCoordinates())
                                    {{ $point->latitude }}, {{ $point->longitude }}
                                @else
                                    <span class="text-gray-400 dark:text-neutral-600">belum diisi</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{-- Klik badge = toggle status cepat (tanpa modal) --}}
                                <button type="button" wire:click="toggleStatus({{ $point->id }})"
                                    class="transition-opacity hover:opacity-80"
                                    title="Klik untuk ubah status">
                                    @if ($point->isConnected())
                                        <x-nawasara-ui::badge color="success" icon="lucide-wifi">terhubung</x-nawasara-ui::badge>
                                    @else
                                        <x-nawasara-ui::badge color="danger" icon="lucide-wifi-off">tidak terhubung</x-nawasara-ui::badge>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-1">
                                    @can('wifi.point.update')
                                        <x-nawasara-ui::icon-button icon="pencil" tooltip="Edit titik"
                                            wire:click="openEdit({{ $point->id }})" />
                                    @endcan
                                    @can('wifi.point.delete')
                                        <x-nawasara-ui::icon-button icon="trash-2" tooltip="Hapus titik"
                                            wire:click="delete({{ $point->id }})"
                                            wire:confirm="Hapus titik WiFi {{ $point->name }}?" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:table>

                <x-slot:footer>
                    <div class="px-2">
                        {{ $this->points->links('nawasara-ui::components.pagination') }}
                    </div>
                </x-slot:footer>
            </x-nawasara-ui::table>
        @endif
    </x-nawasara-ui::page.container>

    {{-- Create / Edit modal --}}
    <x-nawasara-ui::modal wire:model="showForm" maxWidth="xl"
        :title="$editingId ? 'Edit Titik WiFi' : 'Tambah Titik WiFi'"
        subtitle="Data titik hotspot WiFi publik untuk monitoring & peta.">
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-nawasara-ui::form.input label="Nama Titik" wire:model="name"
                    placeholder="WiFi Alun-Alun" />
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-nawasara-ui::form.input label="Lokasi" wire:model="location"
                    placeholder="Alun-Alun Ponorogo sisi utara" />
                @error('location') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Koordinat — untuk plot di peta. Opsional. --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-nawasara-ui::form.input label="Latitude" wire:model="latitude"
                        placeholder="-7.8696" inputmode="decimal" />
                    @error('latitude') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <x-nawasara-ui::form.input label="Longitude" wire:model="longitude"
                        placeholder="111.4625" inputmode="decimal" />
                    @error('longitude') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <x-nawasara-ui::form.select label="Status Koneksi" wire:model="status"
                    :options="['disconnected' => 'Tidak Terhubung', 'connected' => 'Terhubung']"
                    :placeholder="null" />
                @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" wire:model="is_active"
                    class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-700">
                Aktif (tampil di monitoring & peta)
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <x-nawasara-ui::button type="button" variant="ghost" color="secondary"
                    wire:click="$set('showForm', false)">
                    Batal
                </x-nawasara-ui::button>
                <x-nawasara-ui::button type="submit" color="primary">
                    {{ $editingId ? 'Simpan Perubahan' : 'Tambah Titik' }}
                </x-nawasara-ui::button>
            </div>
        </form>
    </x-nawasara-ui::modal>
</div>
