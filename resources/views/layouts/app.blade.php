<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Justice Hub CRM' }}</title>
    @vite(['resources/css/app.css', 'resources/css/justice-hub.css', 'resources/js/app.js'])
</head>
<body class="jh-app" style="margin: 0; min-height: 100vh;">
    <div style="display: flex; min-height: 100vh;">

        {{-- Sidebar --}}
        @include('components.sidebar')

        {{-- Main area --}}
        <div style="flex: 1; display: flex; flex-direction: column; min-width: 0;">

            {{-- Top bar --}}
            @include('components.topbar')

            {{-- Page content --}}
            <main class="jh-scroll" style="flex: 1; overflow-y: auto; background: var(--parchment);">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Alpine.js theme sync --}}
    <script>
        document.addEventListener('alpine:init', () => {
            const saved = localStorage.getItem('jh-theme') || '{{ session("theme", "light") }}';
            document.documentElement.setAttribute('data-theme', saved);
        });
    </script>

    @stack('scripts')
</body>
</html>
