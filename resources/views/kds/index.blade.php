<x-staff-layout title="Kitchen Display System">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl font-semibold text-cream">Kitchen Display System</h2>
            <div class="flex items-center gap-2 text-sm text-cream-muted">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                </span>
                Live · auto-refreshing
            </div>
        </div>
    </x-slot>

    <div x-data="kds()" x-init="start()">
        {{-- Empty queue --}}
        <template x-if="orders.length === 0">
            <div class="rounded-2xl border border-dashed border-espresso-700 bg-espresso-900/40 py-24 text-center">
                <svg class="mx-auto mb-3 h-10 w-10 text-cream-faint" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <p class="text-cream-muted">No active orders. The kitchen queue is clear.</p>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <template x-for="o in orders" :key="o.order_id">
                <div class="flex flex-col overflow-hidden rounded-2xl border bg-espresso-850"
                     :class="o.status === 'Ready' ? 'border-emerald-500/40' : 'border-amber-500/40'">
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b px-4 py-3"
                         :class="o.status === 'Ready' ? 'border-emerald-500/20 bg-emerald-500/10' : 'border-amber-500/20 bg-amber-500/10'">
                        <div>
                            <p class="text-[10px] font-medium uppercase tracking-wide text-cream-faint">Order #<span x-text="o.order_id"></span></p>
                            <p class="font-display text-lg font-bold text-cream">Table <span x-text="o.table_number"></span></p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold"
                                  :class="o.status === 'Ready' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300'"
                                  x-text="o.status"></span>
                            <p class="mt-1 flex items-center justify-end gap-1 text-xs text-cream-muted">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4l3 2"/></svg>
                                <span x-text="elapsed(o.placed_at)"></span>
                            </p>
                        </div>
                    </div>

                    {{-- Items --}}
                    <ul class="flex-1 space-y-2.5 px-4 py-3">
                        <template x-for="(it, i) in o.items" :key="i">
                            <li>
                                <p class="text-sm font-medium text-cream">
                                    <span class="font-bold text-ember" x-text="it.quantity + '×'"></span>
                                    <span x-text="it.name"></span>
                                </p>
                                <p x-show="it.special_instructions" x-cloak
                                   class="mt-0.5 text-xs italic text-rose-300" x-text="'⚠ ' + it.special_instructions"></p>
                            </li>
                        </template>
                    </ul>

                    {{-- Actions --}}
                    <div class="border-t border-espresso-700 p-3">
                        <template x-if="o.status === 'Preparing'">
                            <button @click="advance(o, 'Ready')"
                                class="w-full rounded-lg bg-emerald-600 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                                Mark Ready
                            </button>
                        </template>
                        <template x-if="o.status === 'Ready'">
                            <button @click="advance(o, 'Served')"
                                class="w-full rounded-lg bg-ember py-2 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600">
                                Mark Served
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kds', () => ({
                orders: [],
                now: Date.now(),

                start() {
                    this.refresh();
                    setInterval(() => this.refresh(), 3000);        // FR-05 / NFR-01: ~3s refresh
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
</x-staff-layout>
