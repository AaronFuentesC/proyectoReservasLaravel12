<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Room;
use App\Models\BookingItem;

new #[Title('Analytics')] class extends Component {
    #[Computed]
    public function totalBookings()
    {
        return Booking::all()->count();
    }
    #[Computed]
    public function pendingBookings()
    {
        return Booking::where('state', 'pending')->count();
    }
    #[Computed]
    public function totalItems()
    {
        return Item::all()->count();
    }
    #[Computed]
    public function activeItems()
    {
        return Item::where('active', '1')->count();
    }
    #[Computed]
    public function totalRooms()
    {
        return Room::all()->count();
    }
    #[Computed]
    public function activeRooms()
    {
        return Room::where('active', '1')->count();
    }
    // Componente Livewire
    #[Computed]
    public function topRooms()
    {
        return BookingItem::where('reservable_type', Room::class)
            ->join('rooms', 'booking_items.reservable_id', '=', 'rooms.id')
            ->join('bookings', 'booking_items.booking_id', '=', 'bookings.id') // relacionar con reservas
            ->whereIn('bookings.state', ['pending', 'approved']) // solo pendientes o confirmadas
            ->selectRaw('rooms.name, COUNT(*) as total')
            ->groupBy('rooms.name')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'label' => $r->name,
                'value' => $r->total,
            ])
            ->values();
    }

    #[Computed]
    public function topItems()
    {
        return BookingItem::where('reservable_type', Item::class)
            ->join('items', 'booking_items.reservable_id', '=', 'items.id')
            ->join('bookings', 'booking_items.booking_id', '=', 'bookings.id') // para filtrar por estado
            ->whereIn('bookings.state', ['pending', 'approved']) // solo reservas activas
            ->selectRaw('items.name, SUM(booking_items.quantity) as total') // sumar cantidades
            ->groupBy('items.name')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'label' => $r->name,
                'value' => $r->total,
            ])
            ->values();
    }
    #[Computed]
    public function bookingsPerWeek()
    {
        // Últimas 8 semanas
        $weeks = collect();

        for ($i = 7; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();

            $count = Booking::where('state', 'approved') // solo aprobadas
                ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
                ->count();

            $weeks->push([
                'label' => $startOfWeek->format('d M'), // etiqueta: inicio de la semana
                'value' => $count,
            ]);
        }

        return $weeks;
    }

};
?>

<div class="flex h-full w-full flex-col gap-6 rounded-xl p-4">
    @island()
    <!-- Primer bloque: estadísticas generales -->
    <div class="grid gap-6 md:grid-cols-3">
        <!-- Reservas -->
        <div class="absolute top-0 right-0 p-4">
            <flux:button wire:click="$refresh" icon="arrow-path" variant="subtle" size="sm" label="Actualizar" />
        </div>
        <div
            class="relative flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Reservas</h3>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $this->totalBookings() }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pendientes: {{ $this->pendingBookings() }}</p>
            </div>

        </div>

        <!-- Equipos -->
        <div
            class="relative flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Equipos</h3>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $this->totalItems() }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Activos: {{ $this->activeItems() }}</p>
            </div>
        </div>

        <!-- Salas -->
        <div
            class="relative flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Salas</h3>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $this->totalRooms() }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Activas: {{ $this->activeRooms() }}</p>
            </div>
        </div>
    </div>
    @endisland

    <!-- Segundo bloque: gráficos o top items -->
    {{-- En estos gráficos solo aparecen las salas o los items de cuyas reservas estén en estado pending o approved,
    para saber lo que está reservado actualmente --}}
    <div class="grid gap-6 md:grid-cols-3 mt-6">
        <div
            class="relative flex flex-col rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <h3 class="text-lg font-semibold mb-2">Salas más reservadas actualmente</h3>
            <canvas id="roomsChart" wire:ignore style="width:100%; height:30rem;"
                data-rooms='@json($this->topRooms())'></canvas>
        </div>

        <div
            class="relative flex flex-col justify-center rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Equipos más reservados actualmente
            </h3>
            <canvas id="itemsChart" wire:ignore style="width:100%; height:30rem;"
                data-items='@json($this->topItems())'></canvas>
        </div>

        <div
            class="relative flex flex-col justify-center rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Reservas por semana</h3>
            <canvas id="weeklyChart" wire:ignore style="width:100%; height:30rem;"
                data-weekly='@json($this->bookingsPerWeek())'></canvas>
        </div>
    </div>
</div>
@script
<script>
    setTimeout(() => {
        //Rooms chart
        const roomsCanvas = document.getElementById('roomsChart');
        if (roomsCanvas && window.Chart) {
            const roomsData = JSON.parse(roomsCanvas.dataset.rooms);
            const labels = roomsData.map(r => r.label);
            const values = roomsData.map(r => r.value);

            new window.Chart(roomsCanvas, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Reservas', data: values }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // forzar enteros
                                callback: function(value) {
                                    return Number.isInteger(value) ? value : null;
                                }
                            }
                        }
                    },
                }
            });
        }

        //Items chart
        const itemsCanvas = document.getElementById('itemsChart');
        if (itemsCanvas && window.Chart) {
            const itemsData = JSON.parse(itemsCanvas.dataset.items);
            const labels = itemsData.map(r => r.label);
            const values = itemsData.map(r => r.value);

            new window.Chart(itemsCanvas, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Cantidad', data: values }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // forzar enteros
                                callback: function (value) {
                                    return Number.isInteger(value) ? value : null;
                                }
                            }
                        }
                    },
                }
            });
        }
        //Weekly bookings chart
        const weeklyCanvas = document.getElementById('weeklyChart');
        if (weeklyCanvas && window.Chart) {
            const weeklyData = JSON.parse(weeklyCanvas.dataset.weekly);
            const labels = weeklyData.map(r => r.label);
            const values = weeklyData.map(r => r.value);

            new window.Chart(weeklyCanvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Reservas aprobadas',
                        data: values,
                        fill: false,
                        borderColor: '#3b82f6',
                        backgroundColor: '#3b82f6',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // forzar enteros
                                callback: function (value) {
                                    return Number.isInteger(value) ? value : null;
                                }
                            }
                        }
                    },
                }
            });
        }
    }, 50);
</script>
@endscript