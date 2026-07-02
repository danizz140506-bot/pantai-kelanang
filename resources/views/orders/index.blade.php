<x-staff-layout title="Orders">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl font-semibold text-cream">My Orders · Today</h2>
            <a href="{{ route('tables.index') }}" class="rounded-lg bg-ember px-4 py-2 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600">
                + New Order
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl" x-data="ordersList()" x-init="start()">
        <div class="overflow-hidden rounded-2xl border border-espresso-700 bg-espresso-850">
            <table class="min-w-full divide-y divide-espresso-700 text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-cream-faint">
                        <th class="px-5 py-3">Order</th>
                        <th class="px-5 py-3">Table</th>
                        <th class="px-5 py-3">Time</th>
                        <th class="px-5 py-3">Total</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-espresso-800">
                    <template x-if="orders.length === 0">
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-cream-faint">No orders yet today.</td></tr>
                    </template>
                    <template x-for="o in orders" :key="o.order_id">
                        <tr class="text-cream-muted">
                            <td class="px-5 py-3 font-medium text-cream">#<span x-text="o.order_id"></span></td>
                            <td class="px-5 py-3">Table <span x-text="o.table_number"></span></td>
                            <td class="px-5 py-3 text-cream-faint" x-text="o.time"></td>
                            <td class="px-5 py-3">RM <span x-text="o.total_amount"></span></td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                      :class="{
                                        'bg-amber-500/15 text-amber-300': o.status === 'Preparing',
                                        'bg-emerald-500/15 text-emerald-300': o.status === 'Ready',
                                        'bg-espresso-800 text-cream-muted': o.status === 'Served',
                                      }" x-text="o.status"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
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
</x-staff-layout>
