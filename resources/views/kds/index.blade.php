<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Kitchen Display System</h2>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                </span>
                Live &middot; auto-refreshing
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="kds()" x-init="start()">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <template x-if="orders.length === 0">
                <div class="rounded-xl border border-dashed border-gray-300 bg-white py-20 text-center">
                    <p class="text-gray-400">No active orders. The kitchen queue is clear.</p>
                </div>
            </template>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <template x-for="o in orders" :key="o.order_id">
                    <div class="flex flex-col rounded-xl border bg-white shadow-sm"
                         :class="o.status === 'Ready' ? 'border-emerald-300 ring-1 ring-emerald-100' : 'border-amber-300 ring-1 ring-amber-100'">
                        {{-- Card header --}}
                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3"
                             :class="o.status === 'Ready' ? 'bg-emerald-50' : 'bg-amber-50'">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Order #<span x-text="o.order_id"></span></p>
                                <p class="text-lg font-bold text-gray-800">Table <span x-text="o.table_number"></span></p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                      :class="o.status === 'Ready' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                      x-text="o.status"></span>
                                <p class="mt-1 text-xs text-gray-500" x-text="elapsed(o.placed_at)"></p>
                            </div>
                        </div>

                        {{-- Items --}}
                        <ul class="flex-1 space-y-2 px-4 py-3">
                            <template x-for="(it, i) in o.items" :key="i">
                                <li>
                                    <p class="text-sm font-medium text-gray-800">
                                        <span class="text-gray-500" x-text="it.quantity + '×'"></span>
                                        <span x-text="it.name"></span>
                                    </p>
                                    <p x-show="it.special_instructions" x-cloak
                                       class="text-xs italic text-rose-500" x-text="'⚠ ' + it.special_instructions"></p>
                                </li>
                            </template>
                        </ul>

                        {{-- Actions --}}
                        <div class="border-t border-gray-100 p-3">
                            <template x-if="o.status === 'Preparing'">
                                <button @click="advance(o, 'Ready')"
                                    class="w-full rounded-lg bg-emerald-600 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                    Mark Ready
                                </button>
                            </template>
                            <template x-if="o.status === 'Ready'">
                                <button @click="advance(o, 'Served')"
                                    class="w-full rounded-lg bg-gray-800 py-2 text-sm font-semibold text-white transition hover:bg-gray-900">
                                    Mark Served
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kds', () => ({
                orders: [],
                now: Date.now(),

                start() {
                    this.refresh();
                    setInterval(() => this.refresh(), 3000);   // FR-05 / NFR-01: ~3s refresh
                    setInterval(() => this.now = Date.now(), 1000); // tick elapsed timers
                },

                async refresh() {
                    try {
                        const res = await fetch('{{ route('kds.feed') }}', { headers: { 'Accept': 'application/json' } });
                        if (res.ok) this.orders = await res.json();
                    } catch (e) { /* ignore transient errors */ }
                },

                elapsed(placedAt) {
                    const secs = Math.max(0, Math.floor((this.now - new Date(placedAt).getTime()) / 1000));
                    const m = Math.floor(secs / 60), s = secs % 60;
                    return `${m}m ${String(s).padStart(2, '0')}s`;
                },

                async advance(o, status) {
                    try {
                        const token = document.querySelector('meta[name=csrf-token]').content;
                        const res = await fetch(`/orders/${o.order_id}/status`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ status }),
                        });
                        if (res.ok) this.refresh();
                    } catch (e) { /* ignore */ }
                },
            }));
        });
    </script>
</x-app-layout>
