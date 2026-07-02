<x-staff-layout title="Dashboard">
    <div class="mb-6">
        <h1 class="font-display text-2xl font-semibold text-cream sm:text-3xl">Waiter Dashboard</h1>
        <p class="text-sm text-cream-muted">Welcome back, {{ auth()->user()->full_name }} · {{ now()->format('l, j M Y') }}</p>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4">
        <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">Available Tables</p>
            <p class="mt-1 text-2xl font-bold text-cream">{{ $availableTables }}</p>
        </div>
        <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">Orders Today</p>
            <p class="mt-1 text-2xl font-bold text-cream">{{ $ordersToday }}</p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <a href="{{ route('tables.index') }}" class="group rounded-2xl border border-espresso-700 bg-espresso-850 p-6 transition hover:border-ember/50">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-ember/15 text-ember">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M6 7v10M18 7v10M4 17h16"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-cream">Table Availability &amp; Assignment</h3>
            <p class="mt-1 text-sm text-cream-muted">View live table status and seat parties (FR-02, FR-03).</p>
        </a>
        <a href="{{ route('orders.index') }}" class="group rounded-2xl border border-espresso-700 bg-espresso-850 p-6 transition hover:border-ember/50">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-ember/15 text-ember">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h11M8 12h11M8 18h11M4 6h.01M4 12h.01M4 18h.01"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-cream">Orders</h3>
            <p class="mt-1 text-sm text-cream-muted">Track today's orders and their live status (FR-04, FR-06).</p>
        </a>
    </div>
</x-staff-layout>
