<x-staff-layout title="Dashboard">
    <div class="mb-6">
        <h1 class="font-display text-2xl font-semibold text-cream sm:text-3xl">Cashier Dashboard</h1>
        <p class="text-sm text-cream-muted">Welcome back, {{ auth()->user()->full_name }} · {{ now()->format('l, j M Y') }}</p>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4">
        <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">Awaiting Payment</p>
            <p class="mt-1 text-2xl font-bold text-cream">{{ $awaiting }}</p>
        </div>
        <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">Today's Sales</p>
            <p class="mt-1 text-2xl font-bold text-cream">RM {{ number_format($salesToday, 2) }}</p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="grid grid-cols-1 gap-4">
        <a href="{{ route('billing.index') }}" class="group rounded-2xl border border-espresso-700 bg-espresso-850 p-6 transition hover:border-ember/50">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-ember/15 text-ember">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v10H3zM3 11h18"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-cream">Billing &amp; Payment</h3>
            <p class="mt-1 text-sm text-cream-muted">Generate bills, apply discounts, and process payments (FR-07, FR-08).</p>
        </a>
    </div>
</x-staff-layout>
