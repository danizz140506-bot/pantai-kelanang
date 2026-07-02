<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Receipt &middot; Order #{{ $order->order_id }}</h2>
            <a href="{{ route('billing.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to billing</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-md px-4">
            @if (session('status') === 'paid')
                <div class="mb-4 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Payment successful. Table {{ $order->table?->table_number }} released.
                </div>
            @endif

            {{-- Receipt --}}
            <div id="receipt" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="text-center">
                    <h3 class="font-semibold text-gray-900">Asam Pedas Claypot Pantai Kelanang</h3>
                    <p class="text-xs text-gray-500">Official Receipt</p>
                </div>

                <div class="my-4 border-y border-dashed border-gray-200 py-3 text-xs text-gray-600">
                    <div class="flex justify-between"><span>Order</span><span>#{{ $order->order_id }}</span></div>
                    <div class="flex justify-between"><span>Table</span><span>{{ $order->table?->table_number ?? '—' }}</span></div>
                    <div class="flex justify-between"><span>Cashier</span><span>{{ auth()->user()->full_name }}</span></div>
                    <div class="flex justify-between"><span>Date</span><span>{{ $order->payment->payment_date->format('d M Y, h:i A') }}</span></div>
                </div>

                <ul class="space-y-1.5 text-sm">
                    @foreach ($order->orderItems as $line)
                        <li class="flex justify-between">
                            <span class="text-gray-700">{{ $line->quantity }} &times; {{ $line->menuItem?->name }}</span>
                            <span class="text-gray-700">RM {{ number_format($line->subtotal, 2) }}</span>
                        </li>
                    @endforeach
                </ul>

                @php
                    $deposit = round((float) $order->payment->subtotal - (float) $order->payment->discount_amount - (float) $order->payment->total_amount, 2);
                @endphp
                <div class="mt-4 space-y-1 border-t border-dashed border-gray-200 pt-3 text-sm">
                    <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>RM {{ number_format($order->payment->subtotal, 2) }}</span></div>
                    @if ((float) $order->payment->discount_amount > 0)
                        <div class="flex justify-between text-gray-600"><span>Discount</span><span>− RM {{ number_format($order->payment->discount_amount, 2) }}</span></div>
                    @endif
                    @if ($deposit > 0)
                        <div class="flex justify-between text-gray-600"><span>Deposit paid (reservation)</span><span>− RM {{ number_format($deposit, 2) }}</span></div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-gray-900"><span>Balance Paid</span><span>RM {{ number_format($order->payment->total_amount, 2) }}</span></div>
                </div>

                <div class="mt-4 border-t border-dashed border-gray-200 pt-3 text-xs text-gray-600">
                    <div class="flex justify-between"><span>Payment Method</span><span>{{ $order->payment->payment_method }}</span></div>
                    <div class="flex justify-between"><span>Status</span><span class="font-semibold text-emerald-600">{{ $order->payment->payment_status }}</span></div>
                </div>

                <p class="mt-5 text-center text-xs text-gray-400">Thank you &middot; Terima kasih</p>
            </div>

            <button onclick="window.print()" class="mt-4 w-full rounded-lg border border-gray-300 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                Print Receipt
            </button>
        </div>
    </div>
</x-app-layout>
