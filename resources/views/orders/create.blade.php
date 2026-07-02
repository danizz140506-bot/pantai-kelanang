<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Take Order &middot; Table {{ $table->table_number }}
            </h2>
            <a href="{{ route('tables.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to floor</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="orderCart()">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">

            {{-- Menu --}}
            <div class="space-y-6 lg:col-span-2">
                @foreach ($menu as $category => $items)
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 bg-gray-50 px-5 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $category }}</h3>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            @foreach ($items as $item)
                                <li class="flex items-center justify-between gap-4 px-5 py-3">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $item->name }}</p>
                                        <p class="text-sm text-gray-500">RM {{ number_format($item->price, 2) }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="dec({{ $item->menu_id }})"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-gray-600 transition hover:bg-gray-50">&minus;</button>
                                        <span class="w-6 text-center font-semibold text-gray-800" x-text="qty[{{ $item->menu_id }}] || 0"></span>
                                        <button type="button" @click="inc({{ $item->menu_id }})"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-gray-600 transition hover:bg-gray-50">+</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- Order summary --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6 rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-3">
                        <h3 class="font-semibold text-gray-800">Order Summary</h3>
                        <p class="text-sm text-gray-500">Table {{ $table->table_number }}</p>
                    </div>

                    <div class="max-h-[24rem] overflow-y-auto px-5 py-3">
                        <template x-if="cart.length === 0">
                            <p class="py-8 text-center text-sm text-gray-400">No items added yet.</p>
                        </template>

                        <ul class="space-y-3">
                            <template x-for="line in cart" :key="line.menu_id">
                                <li class="border-b border-gray-50 pb-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">
                                                <span x-text="line.quantity"></span> &times; <span x-text="line.name"></span>
                                            </p>
                                            <p class="text-xs text-gray-500">RM <span x-text="line.subtotal.toFixed(2)"></span></p>
                                        </div>
                                        <button type="button" @click="qty[line.menu_id] = 0"
                                            class="text-xs text-rose-500 hover:text-rose-700">Remove</button>
                                    </div>
                                    <input type="text" x-model="notes[line.menu_id]" placeholder="Special instructions (optional)"
                                        class="mt-2 w-full rounded-lg border-gray-200 text-xs placeholder-gray-400 focus:border-gray-400 focus:ring-0" />
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="border-t border-gray-100 px-5 py-4">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-sm text-gray-500">Total</span>
                            <span class="text-lg font-bold text-gray-800">RM <span x-text="total.toFixed(2)"></span></span>
                        </div>

                        <p x-show="error" x-cloak x-text="error" class="mb-2 text-sm text-rose-600"></p>

                        <button type="button" @click="submit()" :disabled="submitting || cart.length === 0"
                            class="w-full rounded-lg bg-gray-800 py-3 text-sm font-semibold text-white transition hover:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-40">
                            <span x-show="!submitting">Submit Order to Kitchen</span>
                            <span x-show="submitting" x-cloak>Submitting&hellip;</span>
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
                qty: {},
                notes: {},
                error: '',
                submitting: false,

                inc(id) { this.qty[id] = (this.qty[id] || 0) + 1; },
                dec(id) { this.qty[id] = Math.max(0, (this.qty[id] || 0) - 1); },

                get cart() {
                    return this.menuFlat
                        .filter(m => (this.qty[m.menu_id] || 0) > 0)
                        .map(m => ({
                            menu_id: m.menu_id,
                            name: m.name,
                            quantity: this.qty[m.menu_id],
                            subtotal: m.price * this.qty[m.menu_id],
                        }));
                },

                get total() {
                    return this.cart.reduce((sum, l) => sum + l.subtotal, 0);
                },

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
                                items: this.cart.map(l => ({
                                    menu_id: l.menu_id,
                                    quantity: l.quantity,
                                    special_instructions: this.notes[l.menu_id] || null,
                                })),
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
</x-app-layout>
