<x-staff-layout title="Sales Report">
    <x-slot name="header">
        <h2 class="font-display text-2xl font-semibold text-cream">Daily Sales Report</h2>
    </x-slot>

    <div class="mx-auto max-w-5xl">
        {{-- Date selector --}}
        <form method="GET" action="{{ route('reports.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
            <div>
                <label for="date" class="mb-1.5 block text-xs font-medium text-cream-muted">Report date</label>
                <input id="date" name="date" type="date" value="{{ $date }}" max="{{ now()->toDateString() }}"
                       class="rounded-lg border border-espresso-700 bg-espresso-900 px-3.5 py-2 text-sm text-cream focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/20 [color-scheme:dark]" />
            </div>
            <button class="rounded-lg bg-ember px-4 py-2 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600">View</button>
            <p class="ml-auto text-sm text-cream-muted">{{ \Illuminate\Support\Carbon::parse($date)->format('d M Y') }}</p>
        </form>

        {{-- Metric cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
                <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">Total Revenue</p>
                <p class="mt-1 text-2xl font-bold text-cream">RM {{ number_format($revenue, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
                <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">Transactions</p>
                <p class="mt-1 text-2xl font-bold text-cream">{{ $transactions }}</p>
            </div>
            <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
                <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">Average / Order</p>
                <p class="mt-1 text-2xl font-bold text-cream">RM {{ number_format($average, 2) }}</p>
            </div>
        </div>

        {{-- Popular items --}}
        <div class="mt-6 rounded-2xl border border-espresso-700 bg-espresso-850 p-6">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ember">Popular Menu Items</h3>
            @php $maxQty = $popular->max('qty') ?: 1; @endphp
            @forelse ($popular as $row)
                <div class="mb-3">
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium text-cream">{{ $row->menuItem?->name ?? 'Item #'.$row->menu_id }}</span>
                        <span class="text-cream-muted">{{ $row->qty }} sold</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-espresso-900">
                        <div class="h-full rounded-full bg-ember" style="width: {{ round($row->qty / $maxQty * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-cream-faint">No sales recorded for this date.</p>
            @endforelse
        </div>
    </div>
</x-staff-layout>
