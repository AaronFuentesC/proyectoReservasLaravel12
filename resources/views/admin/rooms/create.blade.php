<x-layouts::app>
    {{-- Se crea una zona en la que puedes observar donde te encuentras en cuanto a las rutas y volver a rutas
    anteriores. --}}
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item :href="route('dashboard')">
            Dashboard
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('admin.rooms.index')">
            Salas
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>
            Nueva
        </flux:breadcrumbs.item>
    </flux:breadcrumbs>


    {{-- Se crea un pequeño formulario para crear una nueva reserva. --}}
    <flux:card>
        <form action="{{ route('admin.rooms.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data"
            x-data="{ sending:false }" @submit="sending=true">
            @csrf
            <flux:input label="Nombre de la sala" name="name" value="{{ old('name') }}"
                placeholder="Escribe el nombre de la sala"></flux:input>
            <flux:input label="Ubicación de la sala" name="location" value="{{ old('location') }}"
                placeholder="Escribe la ubicación de la sala"></flux:input>
            <flux:input label="Descripción (opcional)" name="description" value="{{ old('description') }}"
                placeholder="Escribe la descripción de la sala." />
            <flux:input min=1 type="number" label="Capacidad" name="capacity" value="{{old('capacity')}}"
                placeholder="Selecciona la capacidad de la sala"></flux:input>

            {{-- Valor por defecto si no está marcado --}}
            <input type="hidden" name="active" value="0">
            <label class="flex items-center space-x-2">
                <flux:checkbox name="active" value="1" {{ old('active', 1) ? 'checked' : '' }} />
                <span>Activo</span>
            </label>

            <div class="mb-4">
                <label>
                    Comentario de la sala:
                    <flux:textarea rows="auto" name="comment" class="block w-full border rounded p-2">
                        {{ old('comment') }}
                    </flux:textarea>
                </label>
            </div>

            <div class="mb-4">
                <label>Adjuntar archivos</label>

                <input type="file" name="attachment[]" multiple>
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" class="data-loading:opacity-50" x-bind:disabled="sending">
                    <span x-show="!sending">Crear sala</span>
                    <span x-show="sending">Guardando...</span>
                </flux:button>
            </div>
        </form>

    </flux:card>
</x-layouts::app>