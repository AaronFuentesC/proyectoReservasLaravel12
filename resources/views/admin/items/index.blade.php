<x-layouts::app>
    <div class="mb-4 flex justify-between items-center">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')">
                Dashboard
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>
                Equipos
            </flux:breadcrumbs.item>
        </flux:breadcrumbs>
        <a href="{{ route('admin.items.create') }}" class="btn btn-blue text-xs">
            Nuevo equipo
        </a>
    </div>



    {{-- El estilo de este div va a hacer que en móvil se observen en una sola columna, que en tablet se visualice en 2
    columnas y que en pantallas de ordenador se visualicen en 4 columnas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($items as $item)

            <flux:card>
                <div class="flex justify-between">
                    <h1 class="text-xl font-bold">{{ $item['name'] }}</h1>
                    <h2 class="text-sm text-gray-500">{{ $item['id'] }}</h2>
                </div>
                <p>Tipo: {{ $item['type'] }}</p>
                <p>Número de serie: {{ $item['serial_number'] }}</p>
                <p>Estado del item: {{ $item['state']}}</p>
                {{-- Botones centrados al final --}}
                <div class="flex justify-center space-x-4 mt-auto pt-4">
                    <a href="{{ route('admin.items.edit', $item) }}" class="btn btn-blue text-sm px-4 py-2 rounded-lg">
                        Editar
                    </a>
                    <form class="delete-form" action="{{ route('admin.items.destroy', $item) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-red text-sm px-4 py-2 rounded-lg">
                            Eliminar
                        </button>
                    </form>
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

                    Swal.fire(window.getFluxSwalThemeOptions({
                        title: '¿Estás seguro?',
                        text: "¡No podrás revertir esto!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminarlo!',
                        cancelButtonText: 'Cancelar'
                    })).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>


    @endpush
</x-layouts::app>