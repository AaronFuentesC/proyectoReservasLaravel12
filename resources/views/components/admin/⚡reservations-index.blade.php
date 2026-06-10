<?php

use Livewire\Component;
use App\Models\Booking;
use App\BookingState;

new class extends Component {
    public string $statusFilter = '';
    protected $queryString = ['statusFilter'];
    public $bookings = [];

    public function mount()
    {
        $this->loadReservations();
    }

    public function updatedStatusFilter()
    {
        $this->loadReservations();
    }

    public function loadReservations()
    {
        $query = Booking::query()->orderBy('created_at', 'desc');

        if ($this->statusFilter && BookingState::tryFrom($this->statusFilter)) {
            $query->where('state', $this->statusFilter);
        }

        $this->bookings = $query->get();
    }
};
?>
<div class="p-6">
    <!-- Filtro -->
    <div class="mb-4">
        <select wire:model.live="statusFilter" class="border rounded-lg px-3 py-2 text-sm w-48 bg-white dark:bg-zinc-900">
            <option value="">Todos los estados</option>
            <option value="draft">Borrador</option>
            <option value="pending">Pendiente</option>
            <option value="approved">Aprobada</option>
            <option value="rejected">Rechazada</option>
            <option value="cancelled">Cancelada</option>
            <option value="finished">Finalizada</option>
        </select>
    </div>

    <!-- Grid de tarjetas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" wire:sort>
        @foreach ($bookings as $booking)
            <flux:card>
                <div class="flex justify-between">
                    <h1 class="text-xl font-bold">{{ $booking->title }}</h1>
                    <h2 class="text-sm text-gray-500" wire:sort:handle>{{ $booking->id }}</h2>
                </div>
                <p>{{ $booking->description }}</p>
    <div class="mt-2 text-sm">

        <div class="flex items-center gap-2 text-gray-400">

            <span>📅</span>

            <span>
                {{ $booking->date_range }}
            </span>

        </div>


        <div class="flex items-center gap-2 mt-1">

            <span>⏱</span>

            <span
                class="
                    px-2 py-1 text-xs rounded
                    @if($booking->time_status === 'current') bg-green-500 text-white
                    @elseif($booking->time_status === 'past') bg-gray-400 text-white
                    @else bg-blue-400 text-white
                    @endif
                "
            >
                {{ $booking->duration }}
            </span>

            @if($booking->time_status === 'current')

                <span class="text-xs text-green-600 font-semibold">
                    En curso
                </span>

            @elseif($booking->time_status === 'past')

                <span class="text-xs text-gray-400">
                    Finalizada
                </span>

            @else

                <span class="text-xs text-blue-600">
                    Próxima
                </span>

            @endif

        </div>

    </div>
                <p>
                    Estado:

                    @switch($booking->state->value)

                        @case('draft')
                            <span class="px-2 py-1 text-xs bg-gray-300 rounded">
                                Draft
                            </span>
                        @break

                        @case('pending')
                            <span class="px-2 py-1 text-xs bg-yellow-300 rounded">
                                Pending
                            </span>
                        @break

                        @case('approved')
                            <span class="px-2 py-1 text-xs bg-green-400 text-white rounded">
                                Approved
                            </span>
                        @break

                        @case('rejected')
                            <span class="px-2 py-1 text-xs bg-red-500 text-white rounded">
                                Rejected
                            </span>
                        @break

                        @case('cancelled')
                            <span class="px-2 py-1 text-xs bg-gray-500 text-white rounded">
                                Cancelled
                            </span>
                        @break

                        @case('finished')
                            <span class="px-2 py-1 text-xs bg-blue-400 text-white rounded">
                                Finished
                            </span>
                        @break

                    @endswitch

                </p>
                @if($booking->hasInvalidResources())

                    <span class="px-2 py-1 text-xs bg-red-600 text-white rounded">
                        ⚠ Recursos no disponibles
                    </span>

                @endif

                <div class="flex justify-center space-x-4 mt-auto pt-4">
                    <a href="{{ route('admin.bookings.edit', $booking) }}"
                        class="btn btn-blue text-sm px-4 py-2 rounded-lg">
                        Editar
                    </a>
                    <form class="delete-form" action="{{ route('admin.bookings.destroy', $booking) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        {{-- Mantener el filtro actual como query string --}}
                        <input type="hidden" name="statusFilter" value="{{ request('statusFilter', $statusFilter) }}">

                        <button class="btn btn-red text-sm px-4 py-2 rounded-lg">Eliminar</button>
                    </form>
                </div>
            </flux:card>
        @endforeach
    </div>
</div>