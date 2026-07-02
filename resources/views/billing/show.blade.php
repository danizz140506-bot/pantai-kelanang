<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Bill &middot; Order #{{ $order->order_id }} &middot; Table {{ $order->table?->table_number ?? '—' }}
            </h2>
            <a href="{{ route('billing.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to billing</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
            subtotal: {{ (float) $order->total_amount }},
            deposit: {{ $deposit }},
            discount: 0,
            method: 'Cash',
            get afterDiscount() { return Math.max(0, this.subtotal - Math.min(Math.max(0, this.discount || 0), this.subtotal)); },
            get balance() { return Math.max(0, this.afterDiscount - this.deposit); }
        }">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                {{-- Itemised bill --}}
                <div class="px-6 py-5">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Itemised Bill</h3>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($order->orderItems as $line)
                            <li class="flex items-start justify-between py-2.5 text-sm">
                                <div>
                                    <span class="font-medium text-gray-800">{{ $line->quantity }} &times; {{ $line->menuItem?->name }}</span>
                                    @if ($line->special_instructions)
                                        <p class="text-xs italic text-gray-400">{{ $line->special_instructions }}</p>
                                    @endif
                                </div>
                                <span class="text-gray-700">RM {{ number_format($line->subtotal, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if (session('error'))
                    <div class="mx-6 mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Payment --}}
                <form method="POST" action="{{ route('billing.pay', $order) }}" class="border-t border-gray-100 bg-gray-50 px-6 py-5">
                    @csrf

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>RM {{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <label for="discount_amount">Discount (RM)</label>
                            <input id="discount_amount" name="discount_amount" type="number" min="0" step="0.01" x-model.number="discount"
                                   class="w-28 rounded-lg border-gray-300 text-right text-sm focus:border-gray-400 focus:ring-0" placeholder="0.00" />
                        </div>
                        @if ($deposit > 0)
                            <div class="flex items-center justify-between text-emerald-600">
                                <span>Deposit paid (reservation)</span>
                                <span>&minus; RM {{ number_format($deposit, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-800">
                            <span>Balance Due</span>
                            <span>RM <span x-text="balance.toFixed(2)"></span></span>
                        </div>
                    </div>

                    <div class="mt-5">
                        <p class="mb-2 text-sm font-medium text-gray-700">Payment Method</p>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['Cash', 'Card', 'E-Wallet'] as $m)
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="{{ $m }}" x-model="method" class="peer sr-only" @checked($m === 'Cash')>
                                    <span class="block rounded-lg border border-gray-300 py-2.5 text-center text-sm font-medium text-gray-600 transition peer-checked:border-gray-800 peer-checked:bg-gray-800 peer-checked:text-white">
                                        {{ $m }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit"
                        class="mt-6 w-full rounded-lg bg-emerald-600 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Process Payment &amp; Settle
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
