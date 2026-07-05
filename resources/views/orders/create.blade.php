<x-staff-layout title="Take Order">
    <div x-data="orderCart()" x-init="init()">

        {{-- Table strip (floor view — select/switch table, SDD 6.3) --}}
        <div class="mb-5">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-cream-faint">Floor · tap a table to switch</p>
                <a href="{{ route('tables.index') }}" class="text-xs text-cream-muted hover:text-ember">Full floor view &rarr;</a>
            </div>
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($tables as $t)
                    @php
                        $isCurrent = $t->table_id === $table->table_id;
                        $tone = match ($t->status) {
                            'Available' => 'text-emerald-300 border-emerald-500/30',
                            'Reserved' => 'text-amber-300 border-amber-500/30',
                            default => 'text-rose-300 border-rose-500/30',
                        };
                    @endphp
                    <a href="{{ route('orders.create', $t) }}"
                       class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl border text-sm font-bold transition
                              {{ $isCurrent ? 'border-ember bg-ember text-espresso-950 ring-2 ring-ember/40' : $tone.' bg-espresso-900 hover:bg-espresso-800' }}">
                        T{{ $t->table_number }}
                        <span class="mt-0.5 h-1.5 w-1.5 rounded-full {{ $isCurrent ? 'bg-espresso-950/50' : '' }}"></span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Menu --}}
            <div class="lg:col-span-2">
                {{-- Category tabs --}}
                <div class="mb-4 flex flex-wrap gap-2">
                    <template x-for="cat in categories" :key="cat">
                        <button type="button" @click="activeCategory = cat"
                            class="rounded-lg px-4 py-2 text-sm font-semibold transition"
                            :class="activeCategory === cat ? 'bg-ember text-espresso-950' : 'border border-espresso-700 bg-espresso-900 text-cream-muted hover:border-ember/60'"
                            x-text="cat"></button>
                    </template>
                </div>

                {{-- Menu cards --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="m in menuFlat.filter(x => x.category === activeCategory)" :key="m.menu_id">
                        <div class="flex flex-col justify-between rounded-2xl border border-espresso-700 bg-espresso-850 p-4 transition hover:border-ember/40">
                            <div>
                                <p class="font-medium leading-snug text-cream" x-text="m.name"></p>
                                <p class="mt-0.5 text-sm text-ember">RM <span x-text="m.price.toFixed(2)"></span></p>
                            </div>
                            <div class="mt-3">
                                <template x-if="(qty[m.menu_id] || 0) === 0">
                                    <button type="button" @click="inc(m.menu_id)"
                                        class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-ember/50 py-2 text-sm font-semibold text-ember transition hover:bg-ember hover:text-espresso-950">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                                        Add
                                    </button>
                                </template>
                                <template x-if="(qty[m.menu_id] || 0) > 0">
                                    <div class="flex items-center justify-between rounded-lg bg-espresso-900 p-1">
                                        <button type="button" @click="dec(m.menu_id)" class="flex h-8 w-8 items-center justify-center rounded-md text-cream-muted transition hover:bg-espresso-800">&minus;</button>
                                        <span class="text-sm font-bold text-cream" x-text="qty[m.menu_id]"></span>
                                        <button type="button" @click="inc(m.menu_id)" class="flex h-8 w-8 items-center justify-center rounded-md text-cream-muted transition hover:bg-espresso-800">+</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Current Order --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6 flex max-h-[calc(100vh-3rem)] flex-col rounded-2xl border border-espresso-700 bg-espresso-850">
                    <div class="border-b border-espresso-700 px-5 py-4">
                        <h3 class="font-display text-lg font-semibold text-cream">Current Order</h3>
                        <p class="text-sm text-cream-muted">Table {{ $table->table_number }} · Dine-in</p>
                        <p x-show="hasPreorder" x-cloak class="mt-2 flex items-center gap-1.5 rounded-lg border border-amber-500/30 bg-amber-500/10 px-2.5 py-1.5 text-xs text-amber-200">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pre-filled from the customer&rsquo;s online reservation — adjust if needed.
                        </p>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-3">
                        <template x-if="cart.length === 0">
                            <p class="py-10 text-center text-sm text-cream-faint">No items yet. Tap <span class="text-ember">Add</span> on a dish.</p>
                        </template>
                        <ul class="space-y-3">
                            <template x-for="line in cart" :key="line.menu_id">
                                <li class="border-b border-espresso-800 pb-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-cream"><span x-text="line.quantity"></span>× <span x-text="line.name"></span></p>
                                            <p class="text-xs text-cream-muted">RM <span x-text="line.subtotal.toFixed(2)"></span></p>
                                        </div>
                                        <button type="button" @click="qty[line.menu_id] = 0" class="shrink-0 text-xs text-rosewood-text hover:text-rose-300">Remove</button>
                                    </div>
                                    <input type="text" x-model="notes[line.menu_id]" placeholder="Special instructions (optional)"
                                        class="mt-2 w-full rounded-lg border border-espresso-700 bg-espresso-900 px-2.5 py-1.5 text-xs text-cream placeholder-cream-faint focus:border-ember focus:outline-none focus:ring-0" />
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="border-t border-espresso-700 px-5 py-4">
                        {{-- Deposit already paid online → show the outstanding balance --}}
                        <template x-if="deposit > 0">
                            <div class="mb-3 space-y-1.5">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-cream-muted">Order total</span>
                                    <span class="text-cream">RM <span x-text="total.toFixed(2)"></span></span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-cream-muted">Deposit paid (50%)</span>
                                    <span class="text-emerald-300">&minus; RM <span x-text="deposit.toFixed(2)"></span></span>
                                </div>
                                <div class="flex items-center justify-between border-t border-espresso-800 pt-2">
                                    <span class="text-sm font-semibold text-cream">Balance due</span>
                                    <span class="font-display text-2xl font-bold text-ember">RM <span x-text="balance.toFixed(2)"></span></span>
                                </div>
                                <p class="text-[11px] text-cream-faint">Balance before SST — the final bill is calculated at billing.</p>
                            </div>
                        </template>

                        {{-- Walk-in (no deposit) → plain total --}}
                        <template x-if="deposit === 0">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm text-cream-muted">Total</span>
                                <span class="font-display text-2xl font-bold text-cream">RM <span x-text="total.toFixed(2)"></span></span>
                            </div>
                        </template>

                        <p x-show="error" x-cloak x-text="error" class="mb-2 text-sm text-rosewood-text"></p>
                        <button type="button" @click="submit()" :disabled="submitting || cart.length === 0"
                            class="w-full rounded-lg bg-ember py-3 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600 disabled:cursor-not-allowed disabled:opacity-40">
                            <span x-show="!submitting">Submit Order to Kitchen</span>
                            <span x-show="submitting" x-cloak>Submitting…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orderCart', () => ({
                menuFlat: @json($menuFlat),
                preorder: @json($preorder ?? []),
                deposit: {{ $deposit ?? 0 }},
                categories: [],
                activeCategory: '',
                qty: {},
                notes: {},
                error: '',
                submitting: false,

                init() {
                    this.categories = [...new Set(this.menuFlat.map(m => m.category))];
                    this.activeCategory = this.categories[0] || '';

                    // Pre-fill the cart with the customer's online pre-order (FR-01),
                    // limited to items still on the menu.
                    const onMenu = new Set(this.menuFlat.map(m => m.menu_id));
                    this.preorder.forEach(line => {
                        if (onMenu.has(line.menu_id)) this.qty[line.menu_id] = line.quantity;
                    });
                },

                get hasPreorder() { return this.preorder.length > 0; },

                inc(id) { this.qty[id] = (this.qty[id] || 0) + 1; },
                dec(id) { this.qty[id] = Math.max(0, (this.qty[id] || 0) - 1); },

                get cart() {
                    return this.menuFlat
                        .filter(m => (this.qty[m.menu_id] || 0) > 0)
                        .map(m => ({ menu_id: m.menu_id, name: m.name, quantity: this.qty[m.menu_id], subtotal: m.price * this.qty[m.menu_id] }));
                },
                get total() { return this.cart.reduce((sum, l) => sum + l.subtotal, 0); },
                get balance() { return Math.max(0, this.total - this.deposit); },

                async submit() {
                    if (this.cart.length === 0) { this.error = 'Add at least one item.'; return; }
                    this.submitting = true;
                    this.error = '';
                    try {
                        const token = document.querySelector('meta[name=csrf-token]').content;
                        const res = await fetch('{{ route('orders.store') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                table_id: {{ $table->table_id }},
                                items: this.cart.map(l => ({ menu_id: l.menu_id, quantity: l.quantity, special_instructions: this.notes[l.menu_id] || null })),
                            }),
                        });
                        const body = await res.json();
                        if (res.ok && body.ok) { window.location = body.redirect; return; }
                        this.error = body.message || 'Failed to submit order.';
                    } catch (e) {
                        this.error = 'Network error. Please try again.';
                    } finally {
                        this.submitting = false;
                    }
                },
            }));
        });
    </script>
</x-staff-layout>
