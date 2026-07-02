<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Reservations' }} · Asam Pedas Claypot Pantai Kelanang</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none !important;}</style>
    </head>
    <body class="min-h-screen bg-espresso-950 font-sans text-cream antialiased">
        {{-- Top bar --}}
        <header class="border-b border-espresso-700">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ route('reservations.create') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-cream ring-1 ring-ember/50">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-full w-full object-cover" />
                    </span>
                    <span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.2em] text-ember">Restaurant Management</span>
                        <span class="block font-display text-lg font-semibold leading-none text-cream">Asam Pedas Claypot Pantai Kelanang</span>
                    </span>
                </a>
                <a href="{{ route('login') }}" class="rounded-lg border border-espresso-700 px-4 py-2 text-sm font-medium text-cream-muted transition hover:border-ember hover:text-ember">
                    Staff Login
                </a>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
            {{ $slot }}
        </main>

        <footer class="border-t border-espresso-700 py-6 text-center text-xs text-cream-faint">
            Pantai Kelanang Branch · Visit Malaysia Year 2026
        </footer>
    </body>
</html>
