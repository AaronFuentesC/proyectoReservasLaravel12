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
    ];
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
    <script>
        window.getFluxSwalThemeOptions = function(options = {}) {
            function rgbaToRgbValues(rgba) {
                const m = rgba && rgba.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([0-9.]+))?\)/i);
                return m ? [parseInt(m[1], 10), parseInt(m[2], 10), parseInt(m[3], 10), m[4] ? parseFloat(m[4]) : 1] : null;
            }

            function luminance([r, g, b]) {
                const a = [r, g, b].map((v) => {
                    v /= 255;
                    return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
                });
                return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
            }

            function isColorDark(color) {
                const vals = rgbaToRgbValues(color);
                return vals ? luminance(vals) < 0.5 : false;
            }

            const merged = { ...options };
            const darkClass = document.documentElement.classList.contains('dark');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const effectiveBg = getComputedStyle(document.body).backgroundColor || getComputedStyle(document.documentElement).backgroundColor || '#0f172a';
            const darkMode = darkClass || prefersDark || isColorDark(effectiveBg);

            if (darkMode) {
                if (!('background' in merged)) {
                    merged.background = effectiveBg;
                }
                if (!('color' in merged)) {
                    merged.color = getComputedStyle(document.body).color || '#e6eef8';
                }
                if (!('confirmButtonColor' in merged)) {
                    merged.confirmButtonColor = '#2563eb';
                }
                if (!('cancelButtonColor' in merged)) {
                    merged.cancelButtonColor = '#d33';
                }
            }

            return merged;
        };
    </script>
    {{-- Stack para scipts. Se pueden añadir nuevos scipts como se ha hecho por ejemplo en el index cuando se quería
    eliminar una categoría. --}}
    @stack('js')
    {{-- En este caso se está utilizando SweetAlert para mostrar mensajes de confirmación. Si se le manda por sesión la
    clave 'swal' muestra el script con los datos que se le han mandado por pantalla. --}}
    @if (session('swal'))
        <script>
            (function() {
                const baseOptions = @json(session('swal'));

                function getCssVar(name) {
                    try {
                        const v = getComputedStyle(document.documentElement).getPropertyValue(name);
                        return v ? v.trim() : '';
                    } catch (e) {
                        return '';
                    }
                }

                function rgbaToRgbValues(rgba) {
                    if (!rgba) return null;
                    const m = rgba.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([0-9.]+))?\)/i);
                    if (!m) return null;
                    return [parseInt(m[1], 10), parseInt(m[2], 10), parseInt(m[3], 10), m[4] ? parseFloat(m[4]) : 1];
                }

                function luminance([r, g, b]) {
                    const a = [r, g, b].map((v) => {
                        v /= 255;
                        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
                    });
                    return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
                }

                function isColorDark(color) {
                    const vals = rgbaToRgbValues(color);
                    if (!vals) return false;
                    return luminance(vals) < 0.5;
                }

                function findEffectiveBackgroundColor(el) {
                    let node = el;
                    while (node && node !== document) {
                        const bg = getComputedStyle(node).backgroundColor;
                        if (bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent') return bg;
                        node = node.parentElement;
                    }
                    return getComputedStyle(document.body).backgroundColor || getComputedStyle(document.documentElement).backgroundColor || '';
                }

                function deriveColors() {
                    const bgVar = getCssVar('--bg') || getCssVar('--background') || getCssVar('--page-bg');
                    const textVar = getCssVar('--text') || getCssVar('--color') || getCssVar('--foreground');
                    const primaryVar = getCssVar('--primary') || getCssVar('--accent') || getCssVar('--flux-primary');

                    const effectiveBg = bgVar || findEffectiveBackgroundColor(document.body) || '#0f172a';

                    return {
                        background: effectiveBg,
                        color: textVar || getComputedStyle(document.body).color || '#e6eef8',
                        confirmButtonColor: primaryVar || '#2563eb',
                    };
                }

                function waitForTheme(timeout = 3000) {
                    return new Promise((resolve) => {
                        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                        if (document.documentElement.classList.contains('dark') || prefersDark || isColorDark(findEffectiveBackgroundColor(document.body))) {
                            return resolve(true);
                        }

                        let resolved = false;

                        function checkAndResolve() {
                            try {
                                if (document.documentElement.classList.contains('dark') || window.matchMedia('(prefers-color-scheme: dark)').matches || isColorDark(findEffectiveBackgroundColor(document.body))) {
                                    if (!resolved) {
                                        resolved = true;
                                        cleanup();
                                        resolve(true);
                                    }
                                }
                            } catch (e) {}
                        }

                        const events = ['theme:changed', 'themeChanged', 'dark-mode:changed', 'color-scheme-changed', 'flux:theme-changed'];
                        const onEvent = () => checkAndResolve();
                        events.forEach(ev => document.addEventListener(ev, onEvent));

                        const obsHtml = new MutationObserver((mutations) => {
                            for (const m of mutations) {
                                if (m.attributeName === 'class') { checkAndResolve(); }
                            }
                        });

                        const obsBody = new MutationObserver(() => { checkAndResolve(); });

                        obsHtml.observe(document.documentElement, { attributes: true });
                        obsBody.observe(document.body, { attributes: true, attributeFilter: ['class', 'style'] });

                        const interval = setInterval(checkAndResolve, 150);

                        const cleanup = () => {
                            clearInterval(interval);
                            obsHtml.disconnect();
                            obsBody.disconnect();
                            events.forEach(ev => document.removeEventListener(ev, onEvent));
                        };

                        setTimeout(() => {
                            if (!resolved) {
                                resolved = true;
                                cleanup();
                                resolve(document.documentElement.classList.contains('dark') || prefersDark || isColorDark(findEffectiveBackgroundColor(document.body)));
                            }
                        }, timeout);
                    });
                }

                function applyAndFire(isDark) {
                    try {
                        if (isDark) {
                            const colors = deriveColors();
                            if (!('background' in baseOptions)) baseOptions.background = colors.background;
                            if (!('color' in baseOptions)) baseOptions.color = colors.color;
                            if (!('confirmButtonColor' in baseOptions)) baseOptions.confirmButtonColor = colors.confirmButtonColor;
                        }
                    } catch (e) {
                        // ignore and fire with baseOptions
                    }

                    Swal.fire(baseOptions);
                }

                function run() {
                    waitForTheme().then((isDark) => applyAndFire(isDark));
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', run);
                } else {
                    run();
                }
            })();
        </script>
    @endif
</body>

</html>