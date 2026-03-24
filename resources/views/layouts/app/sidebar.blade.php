@php
    /* Se crea un array de grupos que se va a mostrar en el sidebar de la página. Si se quiere añader un nuevo grupo, tan solo hay que crear un nuevo array con un nombre, un icono y una lista de enlaces.
    El current sirve para marcar el enlace como activo cuando el usuario se encuentra en esta ruta. Esto se hace con la funcion request()->routeIs().
    */
    $groups = [
        'Platform' => [
            [
                'name' => 'Dashboard',
                'icon' => 'home', //Flux usa heroicons para los iconos.
                'url' => route('dashboard'),
                'current' => request()->routeIs('dashboard'),
            ],
            [
                'name' => 'Salas',
                'icon' => 'folder',
                'url' => route('admin.rooms.index'),
                'current' => request()->routeIs('admin.rooms.*'),
            ],
            [
                'name' => 'Equipos',
                'icon' => 'newspaper',
                'url' => route('admin.items.index'),
                'current' => request()->routeIs('admin.items.*'),
            ],
            [
                'name' => 'Reservas',
                'icon' => 'tag',
                'url' => route('admin.bookings.index'),
                'current' => request()->routeIs('admin.bookings.*'),
            ]
        ]
    ]
 @endphp



<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    @stack('css')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>


        {{-- Dentro de la barra de navegación del sidebar se crea un foreach para crear el apartado de cada uno de los
        grupos. --}}
        <flux:sidebar.nav>
            @foreach ($groups as $group => $links)

                <flux:sidebar.group :heading="$group" class="grid">
                    {{-- Por cada link que haya dentro del group se crea un nuevo item en el sidebar. --}}
                    @foreach ($links as $link)
                        <flux:sidebar.item :icon="$link['icon']" :href="$link['url']" :current="$link['current']" wire:navigate>
                            {{ __($link['name']) }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            @endforeach

        </flux:sidebar.nav>

        <flux:spacer />


        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
    {{-- Stack para scipts. Se pueden añadir nuevos scipts como se ha hecho por ejemplo en el index cuando se quería
    eliminar una categoría. --}}
    @stack('js')
    {{-- En este caso se está utilizando SweetAlert para mostrar mensajes de confirmación. Si se le manda por sesión la
    clave 'swal' muestra el script con los datos que se le han mandado por pantalla. --}}
    @if (session('swal'))
        <script>
            Swal.fire(
                @json(session('swal')));
        </script>
    @endif
</body>

</html>