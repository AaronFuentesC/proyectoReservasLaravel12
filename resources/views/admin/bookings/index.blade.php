<x-layouts::app>
    <div class="mb-4 flex justify-between items-center">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')">
                Dashboard
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>
                Reservas
            </flux:breadcrumbs.item>
        </flux:breadcrumbs>
        <a href="{{ route('admin.bookings.create') }}" class="btn btn-blue text-xs">
            Nueva Reserva
        </a>
    </div>
    <livewire:admin.reservations-index/> {{-- Componente de livewire reactivo con todas las reservas en tarjetas --}}

    {{-- Script para confimar la eliminación de una reserva --}}
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
        @endpush
</x-layouts::app>