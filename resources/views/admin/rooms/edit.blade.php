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
            Editar
        </flux:breadcrumbs.item>
    </flux:breadcrumbs>


    {{-- Se crea un pequeño formulario para crear una nueva reserva. --}}
    <flux:card>
        <form action="{{ route('admin.rooms.update', $room) }}" method="POST" class="space-y-4"
            enctype="multipart/form-data" x-data="{ sending:false }" @submit="sending=true">
            @csrf
            @method('PUT')
            <flux:input label="Nombre de la sala" name="name" value="{{ old('name', $room->name) }}"
                placeholder="Escribe el nombre de la sala"></flux:input>
            <flux:input label="Ubicación de la sala" name="location" value="{{ old('location', $room->location) }}"
                placeholder="Escribe la ubicación de la sala"></flux:input>
            <flux:input label="Descripción (opcional)" name="description"
                value="{{ old('description', $room->description) }}" placeholder="Escribe la descripción de la sala." />
            <flux:input type="number" min=1 label="Capacidad" name="capacity"
                value="{{old('capacity', $room->capacity)}}" placeholder="Selecciona la capacidad de la sala">
            </flux:input>

            {{-- Valor por defecto si no está marcado --}}
            <input type="hidden" name="active" value="0">

            <label class="flex items-center space-x-2">
                <flux:checkbox name="active" value="1" :checked="old('active', $room->active)" />
                <span>Activo</span>
            </label>
            <div class="mb-4">
                <label>
                    Comentario de la edición de la sala:
                    <flux:textarea rows="auto" name="comment" class="block w-full border rounded p-2">
                        {{ old('comment') }}
                    </flux:textarea>
                </label>
            </div>

            <div class="mb-4">
                <label>Adjuntar archivo</label>

                <input type="file" name="attachment[]" multiple>
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" class="data-loading:opacity-50" x-bind:disabled="sending">
                    <span x-show="!sending">Guardar cambios</span>
                    <span x-show="sending">Guardando...</span>
                </flux:button>
            </div>
        </form>

        @foreach($room->attachments as $attachment)

            <div class="border p-2 mb-2">

                <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">
                    {{ $attachment->original_name }}
                </a>

                <span>
                    {{ $attachment->user->name }}
                </span>

            </div>

        @endforeach


        <div class="mt-6">
            <h3 class="font-bold">Historial de comentarios</h3>

            @foreach($room->comments as $comment)

                <div class="border p-2 mb-2">
                    <strong>{{ $comment->user->name }}</strong>
                    <span>{{ $comment->created_at->format('d-m-Y H:i') }}</span>

                    <p>{{ $comment->description }}</p>
                </div>

            @endforeach

        </div>
    </flux:card>
</x-layouts::app>