<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Billing &middot; Orders Awaiting Payment</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Order</th>
                            <th class="px-5 py-3">Table</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($orders as $order)
                            <tr class="text-sm text-gray-700">
                                <td class="px-5 py-3 font-medium">#{{ $order->order_id }}</td>
                                <td class="px-5 py-3">Table {{ $order->table?->table_number ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        @class([
                                            'bg-amber-100 text-amber-700' => $order->status === 'Preparing',
                                            'bg-emerald-100 text-emerald-700' => $order->status === 'Ready',
                                            'bg-gray-100 text-gray-600' => $order->status === 'Served',
                                        ])">{{ $order->status }}</span>
                                </td>
                                <td class="px-5 py-3 font-semibold">RM {{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('billing.show', $order) }}"
                                       class="inline-flex rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-900">
                                        Generate Bill
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">No orders awaiting payment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
