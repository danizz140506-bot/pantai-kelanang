<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Daily Sales Report</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Date selector --}}
            <form method="GET" action="{{ route('reports.index') }}" class="mb-6 flex items-end gap-3">
                <div>
                    <label for="date" class="mb-1 block text-sm font-medium text-gray-600">Report date</label>
                    <input id="date" name="date" type="date" value="{{ $date }}" max="{{ now()->toDateString() }}"
                           class="rounded-lg border-gray-300 text-sm focus:border-gray-400 focus:ring-0" />
                </div>
                <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900">View</button>
                <p class="ml-auto text-sm text-gray-500">{{ \Illuminate\Support\Carbon::parse($date)->format('d M Y') }}</p>
            </form>

            {{-- Metric cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Total Revenue</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">RM {{ number_format($revenue, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Transactions</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">{{ $transactions }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Average / Order</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">RM {{ number_format($average, 2) }}</p>
                </div>
            </div>

            {{-- Popular items --}}
            <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Popular Menu Items</h3>
                @php $maxQty = $popular->max('qty') ?: 1; @endphp
                @forelse ($popular as $row)
                    <div class="mb-3">
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-700">{{ $row->menuItem?->name ?? 'Item #'.$row->menu_id }}</span>
                            <span class="text-gray-500">{{ $row->qty }} sold</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-gray-800" style="width: {{ round($row->qty / $maxQty * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">No sales recorded for this date.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
