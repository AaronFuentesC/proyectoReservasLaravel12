<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    @stack('css')
    @livewireStyles

</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

        <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Reservas') }}
            </flux:navbar.item>
        </flux:navbar>

        <flux:spacer />


        <x-desktop-user-menu />
    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar collapsible="mobile" sticky
        class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Platform')">
                <flux:sidebar.item icon="layout-grid" :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard')  }}
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:sidebar.item>
        </flux:sidebar.nav>
    </flux:sidebar>

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
    @livewireScripts
</body>

</html>