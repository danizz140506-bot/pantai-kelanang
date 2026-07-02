<x-staff-layout title="Billing">
    @php
        $itemCount = $order->orderItems->count();
        $totalQty = $order->orderItems->sum('quantity');
    @endphp

    <div x-data="{
            subtotal: {{ (float) $order->total_amount }},
            deposit: {{ $deposit }},
            taxRate: {{ $taxRate }},
            discount: 0,
            method: 'Cash',
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

                    <div class="space-y-2.5">
                        @php
                            $methods = [
                                ['name' => 'Cash', 'sub' => 'Physical currency', 'icon' => 'M3 7h18v10H3zM3 11h18M7 15h2'],
                                ['name' => 'Card', 'sub' => 'Debit / credit card', 'icon' => 'M3 7h18v10H3zM3 11h18'],
                                ['name' => 'E-Wallet', 'sub' => 'Touch \'n Go, GrabPay, Boost', 'icon' => 'M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zM11 18h2'],
                            ];
                        @endphp
                        @foreach ($methods as $m)
                            <button type="button" @click="method = '{{ $m['name'] }}'"
                                class="flex w-full items-center gap-3 rounded-xl border p-3 text-left transition"
                                :class="method === '{{ $m['name'] }}' ? 'border-ember bg-ember text-espresso-950' : 'border-espresso-700 bg-espresso-900 hover:border-ember/50'">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                                      :class="method === '{{ $m['name'] }}' ? 'bg-espresso-950/15' : 'bg-espresso-800 text-cream-muted'">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $m['icon'] }}"/></svg>
                                </span>
                                <span class="flex-1">
                                    <span class="block text-sm font-semibold" :class="method === '{{ $m['name'] }}' ? 'text-espresso-950' : 'text-cream'">{{ $m['name'] }}</span>
                                    <span class="block text-[11px]" :class="method === '{{ $m['name'] }}' ? 'text-espresso-950/70' : 'text-cream-faint'">{{ $m['sub'] }}</span>
                                </span>
                                <span x-show="method === '{{ $m['name'] }}'" class="rounded-full bg-espresso-950/20 px-2 py-0.5 text-[10px] font-bold text-espresso-950">Selected</span>
                            </button>
                        @endforeach
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
