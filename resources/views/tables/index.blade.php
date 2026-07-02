<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Table Management</h2>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                </span>
                Live &middot; auto-refreshing
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="tableFloor()" x-init="start()">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Legend + toast --}}
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Available</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Reserved</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Occupied</span>
                </div>
                <div x-show="toast" x-transition x-cloak
                     class="rounded-lg px-3 py-1.5 text-sm font-medium"
                     :class="toastOk ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                     x-text="toast"></div>
            </div>

            {{-- Table grid --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                <template x-for="t in tables" :key="t.table_id">
                    <div class="rounded-xl border bg-white p-4 shadow-sm transition"
                         :class="{
                            'border-emerald-200 ring-1 ring-emerald-100': t.status === 'Available',
                            'border-amber-200 ring-1 ring-amber-100': t.status === 'Reserved',
                            'border-rose-200 ring-1 ring-rose-100': t.status === 'Occupied',
                         }">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Table</p>
                                <p class="text-2xl font-bold text-gray-800" x-text="t.table_number"></p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                  :class="{
                                    'bg-emerald-100 text-emerald-700': t.status === 'Available',
                                    'bg-amber-100 text-amber-700': t.status === 'Reserved',
                                    'bg-rose-100 text-rose-700': t.status === 'Occupied',
                                  }"
                                  x-text="t.status"></span>
                        </div>

                        <div class="mt-2 flex items-center gap-1.5 text-sm text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" />
                            </svg>
                            <span x-text="t.capacity + ' pax'"></span>
                        </div>

                        <div class="mt-4">
                            <template x-if="t.status === 'Available'">
                                <button @click="assign(t)"
                                        class="w-full rounded-lg bg-gray-800 py-2 text-sm font-semibold text-white transition hover:bg-gray-900">
                                    Seat party
                                </button>
                            </template>
                            <template x-if="t.status !== 'Available'">
                                <div class="space-y-2">
                                    <a :href="'/tables/' + t.table_id + '/order'"
                                       class="block w-full rounded-lg bg-gray-800 py-2 text-center text-sm font-semibold text-white transition hover:bg-gray-900">
                                        Take order
                                    </a>
                                    <button @click="release(t)"
                                            class="w-full rounded-lg border border-gray-300 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                                        Release table
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tableFloor', () => ({
                tables: @json($tables),
                toast: '',
                toastOk: true,
                pollId: null,

                start() {
                    // Real-time refresh (FR-02 / NFR-01): poll live statuses every 4s.
                    this.pollId = setInterval(() => this.refresh(), 4000);
                },

                async refresh() {
                    try {
                        const res = await fetch('{{ route('tables.status') }}', { headers: { 'Accept': 'application/json' } });
                        if (res.ok) this.tables = await res.json();
                    } catch (e) { /* ignore transient network errors */ }
                },

                flash(message, ok = true) {
                    this.toast = message;
                    this.toastOk = ok;
                    setTimeout(() => this.toast = '', 2500);
                },

                async post(url) {
                    const token = document.querySelector('meta[name=csrf-token]').content;
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    });
                    return { ok: res.ok, body: await res.json() };
                },

                async assign(t) {
                    const { ok, body } = await this.post(`/tables/${t.table_id}/assign`);
                    this.flash(body.message, ok);
                    this.refresh();
                },

                async release(t) {
                    const { ok, body } = await this.post(`/tables/${t.table_id}/release`);
                    this.flash(body.message, ok);
                    this.refresh();
                },
            }));
        });
    </script>
</x-app-layout>
