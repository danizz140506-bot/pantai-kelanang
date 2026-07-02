<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">My Orders &middot; Today</h2>
            <a href="{{ route('tables.index') }}" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900">
                + New Order
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="ordersList()" x-init="start()">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Order</th>
                            <th class="px-5 py-3">Table</th>
                            <th class="px-5 py-3">Time</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="orders.length === 0">
                            <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">No orders yet today.</td></tr>
                        </template>
                        <template x-for="o in orders" :key="o.order_id">
                            <tr class="text-sm text-gray-700">
                                <td class="px-5 py-3 font-medium">#<span x-text="o.order_id"></span></td>
                                <td class="px-5 py-3">Table <span x-text="o.table_number"></span></td>
                                <td class="px-5 py-3 text-gray-500" x-text="o.time"></td>
                                <td class="px-5 py-3">RM <span x-text="o.total_amount"></span></td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                          :class="{
                                            'bg-amber-100 text-amber-700': o.status === 'Preparing',
                                            'bg-emerald-100 text-emerald-700': o.status === 'Ready',
                                            'bg-gray-100 text-gray-600': o.status === 'Served',
                                          }" x-text="o.status"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ordersList', () => ({
                orders: [],
                start() { this.refresh(); setInterval(() => this.refresh(), 4000); },
                async refresh() {
                    try {
                        const res = await fetch('{{ route('orders.feed') }}', { headers: { 'Accept': 'application/json' } });
                        if (res.ok) this.orders = await res.json();
                    } catch (e) { /* ignore transient errors */ }
                },
            }));
        });
    </script>
</x-app-layout>
