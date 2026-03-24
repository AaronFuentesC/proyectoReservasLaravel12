<x-layouts::public>
        {{-- Se crea una zona en la que puedes observar donde te encuentras en cuanto a las rutas y volver a rutas
    anteriores. --}}
{{--     <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item :href="route('public.bookings.index')">
            Inicio
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('public.bookings.index')">
            Reservas
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>
            Nueva
        </flux:breadcrumbs.item>
    </flux:breadcrumbs> --}}


    {{-- Se crea un pequeño formulario para crear una nueva reserva. --}}
    <flux:card>
        <form action="{{ route('public.bookings.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data" x-data="{ sending:false }" @submit="sending=true">
            @csrf
            <flux:input label="Título de la reserva" name="title" value="{{ old('title') }}"
                placeholder="Escribe el título de la reserva"></flux:input>
            <flux:input label="Descripción de la reserva" name="description" value="{{ old('description') }}"
                placeholder="Escribe la descripción de la reserva" id="description"></flux:input>


            <div class="grid grid-cols-2 gap-4">
                <label>
                    Fecha de inicio
                    <div class="relative max-w-sm">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                            </svg>
                        </div>
                        <input datepicker id="default-datepicker" type="text"
                            class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                            placeholder="Select date" name="date_start" value="{{ old('date_start') }}" datepicker-format="dd-mm-yyyy" data-show-other-months="false">
                    </div>
                </label>

                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Hora de inicio:
                    <div class="relative">
                        <div class="absolute inset-y-0 end-0 top-0 flex items-center pe-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <input type="time" id="time"
                            class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                            min="08:00" max="23:00" value="00:00" required name="time_start" />
                    </div>
                </label>

            </div>


            <div class="grid grid-cols-2 gap-4">
                <label>
                    Fecha de fin
                    <div class="relative max-w-sm">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                            </svg>
                        </div>
                        <input datepicker id="default-datepicker-2" type="text"
                            class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                            placeholder="Select date" name="date_end" value="{{ old('date_end') }}" datepicker-format="dd-mm-yyyy">
                    </div>
                </label>

                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Hora de fin:
                    <div class="relative">
                        <div class="absolute inset-y-0 end-0 top-0 flex items-center pe-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <input type="time" id="time2"
                            class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                            min="08:00" max="23:00" value="00:00" required name="time_end" />
                    </div>
                </label>
            </div>
            <livewire:booking-form />

            <div class="mb-4">
            <label> 
                Comentario de la reserva:
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
                    <span x-show="!sending">Crear reserva</span>
                    <span x-show="sending">Guardando...</span>
                </flux:button>
            </div>
        </form>

    </flux:card>
    @error('date_end')
        <p class="text-red-500 text-sm">
            {{ $message }}
        </p>
    @enderror
    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
    @endpush
</x-layouts::public>