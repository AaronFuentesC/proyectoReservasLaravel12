@if (auth()->check() && auth()->user()->hasRole('admin'))
    <x-layouts::app.sidebar :title="$title ?? null">
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts::app.sidebar>
@else
    <x-layouts::app.header>
        {{ $slot }}
    </x-layouts::app.header>
@endif
