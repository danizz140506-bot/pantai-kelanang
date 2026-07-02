<x-staff-layout title="Billing">
    <x-slot name="header">
        <h2 class="font-display text-2xl font-semibold text-cream">Billing · Orders Awaiting Payment</h2>
    </x-slot>

    <div class="mx-auto max-w-5xl">
        <div class="overflow-hidden rounded-2xl border border-espresso-700 bg-espresso-850">
            <table class="min-w-full divide-y divide-espresso-700 text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-cream-faint">
                        <th class="px-5 py-3">Order</th>
                        <th class="px-5 py-3">Table</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Total</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-espresso-800">
                    @forelse ($orders as $order)
                        <tr class="text-cream-muted">
                            <td class="px-5 py-3 font-medium text-cream">#{{ $order->order_id }}</td>
                            <td class="px-5 py-3">Table {{ $order->table?->table_number ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    @class([
                                        'bg-amber-500/15 text-amber-300' => $order->status === 'Preparing',
                                        'bg-emerald-500/15 text-emerald-300' => $order->status === 'Ready',
                                        'bg-espresso-800 text-cream-muted' => $order->status === 'Served',
                                    ])">{{ $order->status }}</span>
                            </td>
                            <td class="px-5 py-3 font-semibold text-cream">RM {{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('billing.show', $order) }}"
                                   class="inline-flex rounded-lg bg-ember px-4 py-2 text-xs font-semibold text-espresso-950 transition hover:bg-ember-600">
                                    Generate Bill
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-cream-faint">No orders awaiting payment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-staff-layout>
