<x-layouts::app.header :title="$title ?? null">
    <flux:main>
        {{ $slot }}
        @livewireScripts
    </flux:main>
</x-layouts::app.header>
