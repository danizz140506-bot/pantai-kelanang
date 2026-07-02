<x-staff-layout title="Dashboard">
    <div class="mb-6">
        <h1 class="font-display text-2xl font-semibold text-cream sm:text-3xl">Kitchen Dashboard</h1>
        <p class="text-sm text-cream-muted">Welcome back, {{ auth()->user()->full_name }} · {{ now()->format('l, j M Y') }}</p>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4">
        <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">Active Orders</p>
            <p class="mt-1 text-2xl font-bold text-cream">{{ $activeOrders }}</p>
        </div>
        <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">Status</p>
            <p class="mt-1 flex items-center gap-2 text-lg font-bold text-emerald-300">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Live
            </p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="grid grid-cols-1 gap-4">
        <a href="{{ route('kds.index') }}" class="group rounded-2xl border border-espresso-700 bg-espresso-850 p-6 transition hover:border-ember/50">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-ember/15 text-ember">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 21V10m14 11V10M3 10h18M12 3v3m-4 0V4m8 2V4"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-cream">Kitchen Display System (KDS)</h3>
            <p class="mt-1 text-sm text-cream-muted">View incoming orders and update status: Preparing → Ready → Served (FR-05, FR-06).</p>
        </a>
    </div>
</x-staff-layout>
