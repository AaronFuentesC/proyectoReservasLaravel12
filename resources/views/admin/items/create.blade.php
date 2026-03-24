<x-layouts::app>
    {{-- Se crea una zona en la que puedes observar donde te encuentras en cuanto a las rutas y volver a rutas
    anteriores. --}}
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item :href="route('dashboard')">
            Dashboard
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('admin.items.index')">
            Equipos
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>
            Nuevo
        </flux:breadcrumbs.item>
    </flux:breadcrumbs>


    {{-- Se crea un pequeño formulario para crear una nueva reserva. --}}
    <flux:card>
        <form action="{{ route('admin.items.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data"
            x-data="{ sending:false }" @submit="sending=true"> {{-- x-data y @submit se van a utilizar para que no se puedan enviar varias peticiones al mismo tiempo --}}
            @csrf
            <flux:input label="Nombre del equipo" name="name" value="{{ old('name') }}" {{-- Nombre --}}
                placeholder="Escribe el título del equipo"></flux:input>
            <flux:select label="Tipo del equipo" name="type"> {{-- Select con el tipo del equipo --}}
                @foreach(\App\ItemType::cases() as $type)
                    <flux:select.option value="{{ $type->value }}">{{ ucfirst($type->value) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input label="Nº Serie (opcional)" name="serial_number" value="{{ old('serial_number') }}"/> {{-- Número de serie --}}

{{--             <flux:select label="Estado del equipo" name="state"> {{-- Select con el estado del equipo 
                @foreach(\App\ItemState::cases() as $state)
                    <flux:select.option value="{{ $state->value }}">{{ ucfirst($state->value) }}</flux:select.option>
                @endforeach
            </flux:select> --}}
            <flux:radio.group label="Estado del equipo" name="state" variant="segmented">
                @foreach(\App\ItemState::cases() as $state)
                    <flux:radio value="{{ $state->value }}" label="{{ ucfirst($state->value) }}"/>
                @endforeach
            </flux:radio.group>




            <flux:input label="Cantidad del equipo" type="number" min="0" name="quantity" value="{{ old('quantity') }}"> {{-- Cantidad --}}

            </flux:input>

            {{-- Valor por defecto si no está marcado --}}
            <input type="hidden" name="active" value="0">
            <label class="flex items-center space-x-2">
                <flux:checkbox name="active" value="1" {{ old('active', 1) ? 'checked' : '' }} /> {{-- Activo / No activo --}}
                <span>Activo</span>
            </label>
            {{-- Comentario --}}
            <div class="mb-4">
                <label>
                    Comentario del recurso:
                    <flux:textarea rows="auto" name="comment" class="block w-full border rounded p-2" >
                        {{ old('comment') }}
                    </flux:textarea>
                </label>
            </div>

            {{-- Adjuntar archivos --}}
            <div class="mb-4" >
                <label>Adjuntar archivos</label>

                <input type="file" name="attachment[]" multiple>
            </div>

            {{-- Botón para crear el equipo --}}
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" class="data-loading:opacity-50" x-bind:disabled="sending">
                    <span x-show="!sending">Crear equipo</span>
                    <span x-show="sending">Guardando...</span>
                </flux:button>
            </div>
        </form>

    </flux:card>
</x-layouts::app>