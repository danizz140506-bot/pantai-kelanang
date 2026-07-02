<x-staff-layout title="Receipt">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl font-semibold text-cream">Receipt · Order #{{ $order->order_id }}</h2>
            <a href="{{ route('billing.index') }}" class="text-sm text-cream-muted hover:text-ember">&larr; Back to billing</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-md">
        @if (session('status') === 'paid')
            <div class="mb-4 flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Payment successful. Table {{ $order->table?->table_number }} released.
            </div>
        @endif

        {{-- Receipt (paper-styled for print) --}}
        <div id="receipt" class="rounded-xl bg-cream p-6 text-espresso-950 shadow-card">
            <div class="text-center">
                <h3 class="font-semibold">Asam Pedas Claypot Pantai Kelanang</h3>
                <p class="text-xs text-espresso-600">Official Receipt</p>
            </div>

            <div class="my-4 border-y border-dashed border-espresso-600/30 py-3 text-xs text-espresso-800">
                <div class="flex justify-between"><span>Order</span><span>#{{ $order->order_id }}</span></div>
                <div class="flex justify-between"><span>Table</span><span>{{ $order->table?->table_number ?? '—' }}</span></div>
                <div class="flex justify-between"><span>Cashier</span><span>{{ auth()->user()->full_name }}</span></div>
                <div class="flex justify-between"><span>Date</span><span>{{ $order->payment->payment_date->format('d M Y, h:i A') }}</span></div>
            </div>

            <ul class="space-y-1.5 text-sm">
                @foreach ($order->orderItems as $line)
                    <li class="flex justify-between">
                        <span>{{ $line->quantity }} &times; {{ $line->menuItem?->name }}</span>
                        <span>RM {{ number_format($line->subtotal, 2) }}</span>
                    </li>
                @endforeach
            </ul>

            @php
                $sub = (float) $order->payment->subtotal;
                $disc = (float) $order->payment->discount_amount;
                $tax = round(($sub - $disc) * 0.06, 2);   // SST 6% (matches BillingController::TAX_RATE)
                $deposit = round($sub - $disc + $tax - (float) $order->payment->total_amount, 2);
            @endphp
            <div class="mt-4 space-y-1 border-t border-dashed border-espresso-600/30 pt-3 text-sm text-espresso-800">
                <div class="flex justify-between"><span>Subtotal</span><span>RM {{ number_format($sub, 2) }}</span></div>
                @if ($disc > 0)
                    <div class="flex justify-between"><span>Discount</span><span>− RM {{ number_format($disc, 2) }}</span></div>
                @endif
                <div class="flex justify-between"><span>Tax (SST 6%)</span><span>RM {{ number_format($tax, 2) }}</span></div>
                @if ($deposit > 0)
                    <div class="flex justify-between"><span>Deposit paid (reservation)</span><span>− RM {{ number_format($deposit, 2) }}</span></div>
                @endif
                <div class="flex justify-between text-base font-bold text-espresso-950"><span>Balance Paid</span><span>RM {{ number_format($order->payment->total_amount, 2) }}</span></div>
            </div>

            <div class="mt-4 border-t border-dashed border-espresso-600/30 pt-3 text-xs text-espresso-800">
                <div class="flex justify-between"><span>Payment Method</span><span>{{ $order->payment->payment_method }}</span></div>
                <div class="flex justify-between"><span>Status</span><span class="font-semibold text-emerald-700">{{ $order->payment->payment_status }}</span></div>
            </div>

            <p class="mt-5 text-center text-xs text-espresso-600">Thank you · Terima kasih</p>
        </div>

        <button onclick="window.print()" class="mt-4 w-full rounded-lg border border-espresso-700 py-2.5 text-sm font-medium text-cream-muted transition hover:bg-espresso-800">
            Print Receipt
        </button>
    </div>
</x-staff-layout>
