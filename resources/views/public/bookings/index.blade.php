<x-layouts::public>
    <div class="mb-4 flex justify-between items-center">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')">
                Dashboard
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>
                Reservas
            </flux:breadcrumbs.item>
        </flux:breadcrumbs>
        <a href="{{ route('public.bookings.create') }}" class="btn btn-blue text-xs">
            Nueva Reserva
        </a>
    </div>



    {{-- El estilo de este div va a hacer que en móvil se observen en una sola columna, que en tablet se visualice en 2
    columnas y que en pantallas de ordenador se visualicen en 4 columnas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($bookings as $booking)
            @php
                $lastComment = $booking->comments->first(); // ya está ordenado por latest
            @endphp
            <flux:card>
                <div class="flex justify-between">
                    <h1 class="text-xl font-bold">{{ $booking['title'] }}</h1>
                    @if($booking->hasInvalidResources())
                        <span class="text-red-500 font-bold">
                            ⚠
                        </span>
                    @endif
                </div>
                <p>{{ $booking['description'] }}</p>
    <div class="mt-2 text-sm">

        <div class="flex items-center gap-2 text-gray-600">

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

                <span class="text-xs text-gray-500">
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
                {{-- Botones centrados al final --}}
                <div class="flex justify-center space-x-2 mt-auto pt-4">

                    {{-- EDITAR solo en draft --}}
                    @if ($booking->state === \App\BookingState::draft)
                        <a href="{{ route('public.bookings.edit', $booking) }}"
                            class="btn btn-blue text-sm px-3 py-2 rounded-lg">
                            Editar
                        </a>
                    @endif


                    {{-- PUBLICAR solo en draft --}}
                    @if ($booking->state === \App\BookingState::draft)

                        @if(!$booking->hasInvalidResources())

                            <form class="publish-form" action="{{ route('public.bookings.publish', $booking) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <button class="btn btn-green text-sm px-3 py-2 rounded-lg">
                                    Publicar
                                </button>

                            </form>

                        @else
                            <flux:tooltip content="No se puede publicar debido a que hay recursos no disponibles" position="bottom">
                            <button
                                class="btn btn-gray text-sm px-3 py-2 rounded-lg opacity-50 cursor-not-allowed"
                                disabled
                            >
                                Publicar
                            </button>
                            </flux:tooltip>

                        @endif

                    @endif


                    {{-- ELIMINAR solo en draft --}}
                    @if ($booking->state === \App\BookingState::draft)

                        <form class="delete-form" action="{{ route('public.bookings.destroy', $booking) }}" method="POST">

                            @csrf
                            @method('DELETE')

                            <button class="btn btn-red text-sm px-3 py-2 rounded-lg">
                                Eliminar
                            </button>

                        </form>

                    @endif


                    {{-- CANCELAR solo en pending o approved --}}
                    @if (
                            in_array($booking->state, [
                                \App\BookingState::pending,
                                \App\BookingState::approved
                            ])
                        )

                        <form class="cancel-form" action="{{ route('public.bookings.cancel', $booking) }}" method="POST">

                            @csrf
                            @method('PUT')

                            <button class="btn btn-yellow text-sm px-3 py-2 rounded-lg">
                                Cancelar
                            </button>

                        </form>

                    @endif
                    @if($booking->state === \App\BookingState::rejected && $lastComment && $lastComment->user->hasRole('admin'))
                        <p class="mt-1 text-sm text-gray-700">
                            <strong>Motivo:</strong> {{ $lastComment->description }}
                        </p>
                    @endif
                </div>
                <div>
                    @if($booking->state === \App\BookingState::approved && $lastComment && $lastComment->user->hasRole('admin'))
                        <p class="mt-1 text-sm text-gray-700">
                            <strong>Anotación:</strong> {{ $lastComment->description }}
                        </p>
                    @endif
                </div>
            </flux:card>
        @endforeach
    </div>
    @push('js')
        <script>
            forms = document.querySelectorAll('.delete-form');

            forms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "¡No podrás revertir esto!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminarlo!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
        <script>
            forms = document.querySelectorAll('.publish-form');

            forms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '¿Quieres publicar esta reserva?',
                        text: "¡Tendrás que esperar a que un administrador la confirme!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, publícala!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
        <script>
            forms = document.querySelectorAll('.cancel-form');

            forms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '¿Quieres cancelar esta reserva?',
                        text: "¡No podrás interactuar con ella a no ser que contactes con un administrador!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, cancélala!',
                        cancelButtonText: 'No cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-layouts::public>