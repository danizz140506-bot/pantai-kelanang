<x-staff-layout title="Table Management">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl font-semibold text-cream">Table Management</h2>
            <div class="flex items-center gap-2 text-sm text-cream-muted">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                </span>
                Live · auto-refreshing
            </div>
        </div>
    </x-slot>

    <div x-data="tableFloor()" x-init="start()">
        {{-- Legend + toast --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-4 text-sm text-cream-muted">
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Available</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Reserved</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Occupied</span>
            </div>
            <div x-show="toast" x-transition x-cloak
                 class="rounded-lg border px-3 py-1.5 text-sm font-medium"
                 :class="toastOk ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' : 'border-rosewood-border bg-rosewood-bg/40 text-rosewood-text'"
                 x-text="toast"></div>
        </div>

        {{-- Table grid --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            <template x-for="t in tables" :key="t.table_id">
                <div class="rounded-2xl border p-4 transition" :class="cardClass(t)">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cream-faint">Table</p>
                            <p class="font-sans text-2xl font-bold text-cream" x-text="t.table_number"></p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold" :class="badgeClass(t)" x-text="t.status"></span>
                    </div>

                    {{-- table + person icons (layout matches capacity) --}}
                    <svg viewBox="0 0 120 120" class="mx-auto my-1 h-20 w-20" :class="iconColor(t)" fill="none">
                        <rect x="34" y="40" width="52" height="40" rx="10" stroke="currentColor" stroke-width="4" fill="currentColor" fill-opacity="0.14" />
                        <g x-show="t.capacity <= 2" fill="currentColor">
                            <g transform="translate(60,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                            <g transform="translate(60,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                        </g>
                        <g x-show="t.capacity > 2 && t.capacity <= 4" fill="currentColor">
                            <g transform="translate(44,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                            <g transform="translate(76,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                            <g transform="translate(44,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                            <g transform="translate(76,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                        </g>
                        <g x-show="t.capacity > 4" fill="currentColor">
                            <g transform="translate(44,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                            <g transform="translate(76,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                            <g transform="translate(15,62)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                            <g transform="translate(105,62)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                            <g transform="translate(44,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                            <g transform="translate(76,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                        </g>
                    </svg>

                    <p class="mb-3 text-center text-xs font-medium text-cream-muted" x-text="t.capacity + ' pax'"></p>

                    <template x-if="t.status === 'Available'">
                        <button @click="assign(t)"
                                class="w-full rounded-lg bg-ember py-2 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600">
                            Seat party
                        </button>
                    </template>
                    <template x-if="t.status !== 'Available'">
                        <div class="space-y-2">
                            <a :href="'/tables/' + t.table_id + '/order'"
                               class="block w-full rounded-lg bg-ember py-2 text-center text-sm font-semibold text-espresso-950 transition hover:bg-ember-600">
                                Take order
                            </a>
                            <button @click="release(t)"
                                    class="w-full rounded-lg border border-espresso-700 py-2 text-sm font-medium text-cream-muted transition hover:bg-espresso-800">
                                Release table
                            </button>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tableFloor', () => ({
                tables: @json($tables),
                toast: '',
                toastOk: true,
                pollId: null,

                start() { this.pollId = setInterval(() => this.refresh(), 4000); }, // FR-02 / NFR-01

                async refresh() {
                    try {
                        const res = await fetch('{{ route('tables.status') }}', { headers: { 'Accept': 'application/json' } });
                        if (res.ok) this.tables = await res.json();
                    } catch (e) { /* ignore transient network errors */ }
                },

                cardClass(t) {
                    if (t.status === 'Available') return 'border-emerald-500/30 bg-emerald-500/5';
                    if (t.status === 'Reserved') return 'border-amber-500/30 bg-amber-500/5';
                    return 'border-rose-500/30 bg-rose-500/5';
                },
                badgeClass(t) {
                    if (t.status === 'Available') return 'bg-emerald-500/15 text-emerald-300';
                    if (t.status === 'Reserved') return 'bg-amber-500/15 text-amber-300';
                    return 'bg-rose-500/15 text-rose-300';
                },
                iconColor(t) {
                    if (t.status === 'Available') return 'text-emerald-400/80';
                    if (t.status === 'Reserved') return 'text-amber-400/80';
                    return 'text-rose-400/80';
                },

                flash(message, ok = true) {
                    this.toast = message;
                    this.toastOk = ok;
                    setTimeout(() => this.toast = '', 2500);
                },
                async post(url) {
                    const token = document.querySelector('meta[name=csrf-token]').content;
                    const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
                    return { ok: res.ok, body: await res.json() };
                },
                async assign(t) { const { ok, body } = await this.post(`/tables/${t.table_id}/assign`); this.flash(body.message, ok); this.refresh(); },
                async release(t) { const { ok, body } = await this.post(`/tables/${t.table_id}/release`); this.flash(body.message, ok); this.refresh(); },
            }));
        });
    </script>
</x-staff-layout>
