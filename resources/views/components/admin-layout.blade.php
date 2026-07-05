@props(['title' => 'Dashboard'])

@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'M4 5h6v6H4zM14 5h6v4h-6zM14 13h6v6h-6zM4 15h6v4H4z'],
        ['label' => 'Tables', 'route' => 'tables.index', 'active' => 'tables.*', 'icon' => 'M4 7h16M6 7v10M18 7v10M4 17h16'],
        ['label' => 'Orders', 'route' => 'orders.index', 'active' => 'orders.index', 'icon' => 'M8 6h11M8 12h11M8 18h11M4 6h.01M4 12h.01M4 18h.01'],
        ['label' => 'Kitchen Display', 'route' => 'kds.index', 'active' => 'kds.*', 'icon' => 'M5 21V10m14 11V10M3 10h18M12 3v3m-4 0V4m8 2V4'],
        ['label' => 'Billing', 'route' => 'billing.index', 'active' => 'billing.*', 'icon' => 'M3 7h18v10H3zM3 11h18'],
        ['label' => 'Reports', 'route' => 'reports.index', 'active' => 'reports.*', 'icon' => 'M4 19V5m0 14h16M8 15v-4m4 4V9m4 6v-6'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo-icon.png') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} · Asam Pedas Claypot Pantai Kelanang</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none !important;}</style>
    </head>
    <body class="min-h-screen bg-espresso-950 font-sans text-cream antialiased" x-data="{ sidebar: false }">

        {{-- Mobile top bar --}}
        <div class="sticky top-0 z-30 flex items-center justify-between border-b border-espresso-700 bg-espresso-950/95 px-4 py-3 backdrop-blur lg:hidden">
            <span class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-cream ring-1 ring-ember/50">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="h-full w-full object-cover" />
                </span>
                <span class="font-display text-base font-semibold text-cream">Owner Panel</span>
            </span>
            <button @click="sidebar = true" class="rounded-lg border border-espresso-700 p-2 text-cream-muted">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        {{-- Backdrop (mobile) --}}
        <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-black/60 lg:hidden"></div>

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-espresso-700 bg-espresso-900 transition-transform duration-200 lg:translate-x-0"
               :class="sidebar && 'translate-x-0'">
            <div class="flex items-center gap-3 border-b border-espresso-700 px-5 py-5">
                <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-cream ring-1 ring-ember/50">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="h-full w-full object-cover" />
                </span>
                <div class="min-w-0">
                    <p class="text-[9px] font-semibold uppercase tracking-[0.2em] text-ember">Owner Panel</p>
                    <p class="truncate font-display text-sm font-semibold text-cream">Asam Pedas Claypot</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                @foreach ($nav as $item)
                    @php $isActive = request()->routeIs($item['active']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive ? 'bg-ember text-espresso-950' : 'text-cream-muted hover:bg-espresso-800 hover:text-cream' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-espresso-700 p-3">
                <div class="flex items-center gap-3 rounded-lg px-3 py-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-ember/15 text-sm font-bold text-ember">
                        {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-cream">{{ auth()->user()->full_name }}</p>
                        <p class="text-[11px] text-cream-faint">Owner</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button title="Log out" class="rounded-lg p-2 text-cream-faint transition hover:bg-espresso-800 hover:text-rosewood-text">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5-5-5M21 12H9M9 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="lg:pl-64">
            <main class="mx-auto max-w-6xl p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
