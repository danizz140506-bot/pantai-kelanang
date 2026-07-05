<x-staff-layout title="Billing">
    @php
        $itemCount = $order->orderItems->count();
        $totalQty = $order->orderItems->sum('quantity');
        // Payment methods with a distinct icon each (heroicons: banknotes / card / qr-code).
        $methods = [
            ['name' => 'Cash', 'sub' => 'Physical currency', 'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m0 0v.375c0 .621.504 1.125 1.125 1.125H18.75m-.75-9v-.375c0-.621.504-1.125 1.125-1.125H21m-.75 9v.75a.75.75 0 0 0 .75.75h.75M18 6.75h.008v.008H18V6.75Zm-12 0h.008v.008H6V6.75Zm12 6.75h.008v.008H18V13.5Zm-12 0h.008v.008H6V13.5Zm6-3.375a2.625 2.625 0 1 1 0 5.25 2.625 2.625 0 0 1 0-5.25Z'],
            ['name' => 'Card', 'sub' => 'Debit / credit card', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 18.75Z'],
            ['name' => 'QR', 'sub' => 'DuitNow QR · TNG, GrabPay, Boost', 'icon' => 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5ZM6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z'],
        ];
    @endphp

    <div x-data="{
            subtotal: {{ (float) $order->total_amount }},
            deposit: {{ $deposit }},
            taxRate: {{ $taxRate }},
            discount: 0,
            method: 'Cash',
            open: false,
            methods: @js($methods),
            get selected() { return this.methods.find(m => m.name === this.method) || this.methods[0]; },
            round2(n) { return Math.round(n * 100) / 100; },
            get taxable() { return Math.max(0, this.subtotal - Math.min(Math.max(0, this.discount || 0), this.subtotal)); },
            get tax() { return this.round2(this.taxable * this.taxRate); },
            get grandTotal() { return this.round2(this.taxable + this.tax); },
            get balance() { return Math.max(0, this.round2(this.grandTotal - this.deposit)); },
            staffDiscount() { this.discount = this.round2(this.subtotal * 0.05); },
            methodHint() {
                if (this.method === 'Cash') return 'Collect physical cash and give change';
                if (this.method === 'Card') return 'Tap / insert card on the reader — Contactless · Chip · Swipe';
                return 'Scan QR — Touch \'n Go, GrabPay, Boost';
            },
        }">

        {{-- Context bar --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-espresso-700 bg-espresso-900 px-5 py-3">
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-cream-faint">Table</p>
                    <p class="font-display text-lg font-bold text-ember">{{ $order->table?->table_number ?? '—' }}</p>
                </div>
                <span class="h-8 w-px bg-espresso-700"></span>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-cream-faint">Order</p>
                    <p class="text-sm font-semibold text-cream">#{{ $order->order_id }} · Dine-in</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-sm text-cream-muted">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-ember/15 text-xs font-bold text-ember">{{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}</span>
                {{ auth()->user()->full_name }}
            </div>
        </div>

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-rosewood-border bg-rosewood-bg/40 px-4 py-3 text-sm text-rosewood-text">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('billing.pay', $order) }}" class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            @csrf
            <input type="hidden" name="discount_amount" :value="discount">
            <input type="hidden" name="payment_method" :value="method">

            {{-- Order Summary (left) --}}
            <div class="flex flex-col rounded-2xl border border-espresso-700 bg-espresso-850 lg:col-span-2">
                <div class="border-b border-espresso-700 px-6 py-4">
                    <h2 class="font-display text-xl font-semibold text-cream">Order Summary</h2>
                    <p class="text-sm text-cream-muted">Review before processing payment</p>
                </div>

                {{-- Items table --}}
                <div class="flex-1 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-espresso-700 text-left text-[10px] font-semibold uppercase tracking-wide text-cream-faint">
                                <th class="px-6 py-2.5">Item</th>
                                <th class="px-3 py-2.5 text-center">Qty</th>
                                <th class="px-3 py-2.5 text-right">Unit</th>
                                <th class="px-6 py-2.5 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-espresso-800">
                            @foreach ($order->orderItems as $line)
                                <tr>
                                    <td class="px-6 py-3">
                                        <p class="font-medium text-cream">{{ $line->menuItem?->name }}</p>
                                        @if ($line->special_instructions)
                                            <p class="text-xs italic text-cream-faint">{{ $line->special_instructions }}</p>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center font-semibold text-ember">{{ $line->quantity }}×</td>
                                    <td class="px-3 py-3 text-right text-cream-muted">RM {{ number_format($line->menuItem?->price ?? 0, 2) }}</td>
                                    <td class="px-6 py-3 text-right font-semibold text-cream">RM {{ number_format($line->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="space-y-2 border-t border-espresso-700 px-6 py-5 text-sm">
                    <div class="flex items-center justify-between text-cream-muted">
                        <span>Subtotal</span>
                        <span class="font-medium text-cream">RM {{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-cream-muted">
                        <span class="flex items-center gap-2">
                            Discount
                            <button type="button" @click="staffDiscount()" class="rounded-full bg-ember/15 px-2 py-0.5 text-[10px] font-semibold text-ember transition hover:bg-ember/25">Staff 5%</button>
                        </span>
                        <span class="flex items-center gap-1.5">
                            − RM
                            <input type="number" min="0" step="0.01" x-model.number="discount"
                                   class="w-20 rounded-lg border border-espresso-700 bg-espresso-900 px-2 py-1 text-right text-sm text-cream focus:border-ember focus:outline-none focus:ring-0" placeholder="0.00" />
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-cream-muted">
                        <span>Tax (SST 6%)</span>
                        <span>RM <span x-text="tax.toFixed(2)"></span></span>
                    </div>
                    <template x-if="deposit > 0">
                        <div class="flex items-center justify-between text-emerald-300">
                            <span>Deposit paid (reservation)</span>
                            <span>− RM {{ number_format($deposit, 2) }}</span>
                        </div>
                    </template>
                    <div class="flex items-center justify-between border-t border-espresso-700 pt-3">
                        <span class="text-base font-semibold text-cream">Total Amount</span>
                        <span class="font-display text-3xl font-bold text-ember">RM <span x-text="grandTotal.toFixed(2)"></span></span>
                    </div>
                </div>
            </div>

            {{-- Payment Method (right) --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6 rounded-2xl border border-espresso-700 bg-espresso-850 p-5">
                    <h2 class="font-display text-lg font-semibold text-cream">Payment Method</h2>
                    <p class="mb-4 text-sm text-cream-muted">Select how the customer will pay</p>

                    {{-- Payment method dropdown --}}
                    <div class="relative" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                            class="flex w-full items-center gap-3 rounded-xl border border-espresso-700 bg-espresso-900 p-3 text-left transition hover:border-ember/50"
                            :class="open && 'border-ember/60'">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ember/15 text-ember">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="selected.icon"/></svg>
                            </span>
                            <span class="flex-1">
                                <span class="block text-sm font-semibold text-cream" x-text="selected.name"></span>
                                <span class="block text-[11px] text-cream-faint" x-text="selected.sub"></span>
                            </span>
                            <svg class="h-4 w-4 text-cream-muted transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </button>

                        <div x-show="open" x-cloak x-transition.origin.top.duration.150ms
                             class="absolute z-20 mt-2 w-full overflow-hidden rounded-xl border border-espresso-700 bg-espresso-900 shadow-2xl">
                            <template x-for="m in methods" :key="m.name">
                                <button type="button" @click="method = m.name; open = false"
                                    class="flex w-full items-center gap-3 p-3 text-left transition"
                                    :class="method === m.name ? 'bg-ember text-espresso-950' : 'hover:bg-espresso-800'">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                                          :class="method === m.name ? 'bg-espresso-950/15 text-espresso-950' : 'bg-espresso-800 text-cream-muted'">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="m.icon"/></svg>
                                    </span>
                                    <span class="flex-1">
                                        <span class="block text-sm font-semibold" :class="method === m.name ? 'text-espresso-950' : 'text-cream'" x-text="m.name"></span>
                                        <span class="block text-[11px]" :class="method === m.name ? 'text-espresso-950/70' : 'text-cream-faint'" x-text="m.sub"></span>
                                    </span>
                                    <svg x-show="method === m.name" class="h-4 w-4 text-espresso-950" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>

                    <p class="mt-3 flex items-center gap-2 rounded-lg border border-espresso-700 bg-espresso-900 px-3 py-2 text-[11px] text-cream-muted">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        <span x-text="methodHint()"></span>
                    </p>

                    {{-- Receipt preview --}}
                    <div class="mt-4 rounded-xl border border-espresso-700 bg-espresso-900 p-3 text-xs">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="font-semibold uppercase tracking-wide text-cream-faint">Receipt Preview</span>
                            <span class="text-cream-faint">Prints after payment</span>
                        </div>
                        <div class="flex justify-between text-cream-muted"><span>Order #{{ $order->order_id }}</span><span>Table {{ $order->table?->table_number ?? '—' }}</span></div>
                        <div class="flex justify-between text-cream-muted"><span>{{ now()->format('d M Y · h:i A') }}</span></div>
                        <div class="mt-1 flex justify-between border-t border-espresso-800 pt-1 text-cream"><span>{{ $itemCount }} items · {{ $totalQty }} pcs</span><span class="font-semibold">RM <span x-text="grandTotal.toFixed(2)"></span></span></div>
                    </div>

                    {{-- Amount due --}}
                    <div class="mt-4 flex items-center justify-between rounded-xl bg-espresso-900 px-4 py-3">
                        <span class="text-sm font-medium text-cream-muted">Amount Due</span>
                        <span class="font-display text-2xl font-bold text-ember">RM <span x-text="balance.toFixed(2)"></span></span>
                    </div>

                    <button type="submit"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-ember py-3.5 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Process Payment
                    </button>
                    <a href="{{ route('billing.index') }}" class="mt-2 block text-center text-xs text-cream-faint transition hover:text-rosewood-text">✕ Cancel Order</a>
                </div>
            </div>
        </form>
    </div>
</x-staff-layout>
