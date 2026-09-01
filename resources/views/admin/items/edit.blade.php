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
            Editar
        </flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Se crea un pequeño formulario para crear una nueva reserva. --}}
    <flux:card>
        <form action="{{ route('admin.items.update', $item) }}" method="POST" class="space-y-4"
            enctype="multipart/form-data" x-data="{ sending:false }" @submit="sending=true">
            @csrf
            @method('PUT')
            <flux:input label="Nombre del equipo" name="name" value="{{ old('name', $item->name) }}"
                placeholder="Escribe el título del equipo"></flux:input>
            <flux:select label="Tipo del equipo" name="type">
                @foreach(\App\ItemType::cases() as $type)
                    <option value="{{ $type->value }}" {{ old('type', $item->type?->value) === $type->value ? 'selected' : '' }}>
                        {{ ucfirst($type->value) }}
                    </option>
                @endforeach
            </flux:select>


            <flux:input label="Nº Serie (opcional)" name="serial_number"
                value="{{ old('serial_number', $item->serial_number) }}" />


            <flux:select label="Estado del equipo" name="state">
                @foreach(\App\ItemState::cases() as $state)
                    <option value="{{ $state->value }}" {{ old('state', $item->state?->value) === $state->value ? 'selected' : '' }}>
                        {{ ucfirst($state->value) }}
                    </option>
                @endforeach
            </flux:select>
            <flux:input label="Cantidad del equipo" type="number" min="0" name="quantity"
                value="{{ old('quantity', $item->quantity) }}"></flux:input>


            {{-- Valor por defecto si no está marcado --}}
            <input type="hidden" name="active" value="0">

            <label class="flex items-center space-x-2">
                <flux:checkbox name="active" value="1" :checked="old('active', $item->active)" />
                <span>Activo</span>
            </label>

            <div class="mb-4">
                <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300">
                    Comentario de la edición del recurso:
                    <flux:textarea rows="auto" name="comment"
                        class="mt-2 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-white/5 dark:text-zinc-50 dark:placeholder:text-zinc-400">
                        {{ old('comment') }}
                    </flux:textarea>
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300">Adjuntar archivos</label>

                <input type="file" name="attachment[]" multiple
                    class="mt-2 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm file:mr-4 file:rounded file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-indigo-500 dark:border-white/10 dark:bg-white/5 dark:text-zinc-50">
            </div>


            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" class="data-loading:opacity-50" x-bind:disabled="sending">
                    <span x-show="!sending">Guardar cambios</span>
                    <span x-show="sending">Guardando...</span>
                </flux:button>
            </div>
        </form>

        @foreach($item->attachments as $attachment)

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

            @foreach($item->comments as $comment)

                <div class="border p-2 mb-2">
                    <strong>{{ $comment->user->name }}</strong>
                    <span>{{ $comment->created_at->format('d-m-Y H:i') }}</span>

                    <p>{{ $comment->description }}</p>
                </div>

            @endforeach

        </div>

    </flux:card>
</x-layouts::app>