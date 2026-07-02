<x-public-layout title="Make a Reservation">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 text-center">
            <h1 class="font-display text-3xl font-semibold text-cream sm:text-4xl">Reserve a Table</h1>
            <p class="mt-1 text-sm text-cream-muted">Book in a minute — no account needed. A 50% deposit secures your table; settle the rest at the restaurant.</p>
        </div>

        <form method="POST" action="{{ route('reservations.store') }}" x-data="reservation()" x-init="init()">
            @csrf
            <input type="hidden" name="table_id" :value="selectedTable">
            <template x-for="(line, idx) in cart" :key="'in-' + line.menu_id">
                <span>
                    <input type="hidden" :name="`items[${idx}][menu_id]`" :value="line.menu_id">
                    <input type="hidden" :name="`items[${idx}][quantity]`" :value="line.quantity">
                </span>
            </template>

            {{-- Step indicator --}}
            <div class="mb-6 flex items-center justify-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold transition"
                          :class="step >= 1 ? 'bg-ember text-espresso-950' : 'bg-espresso-800 text-cream-faint'">1</span>
                    <span class="text-xs font-medium" :class="step >= 1 ? 'text-cream' : 'text-cream-faint'">Details &amp; Table</span>
                </div>
                <span class="h-px w-8 bg-espresso-700 sm:w-16"></span>
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold transition"
                          :class="step >= 2 ? 'bg-ember text-espresso-950' : 'bg-espresso-800 text-cream-faint'">2</span>
                    <span class="text-xs font-medium" :class="step >= 2 ? 'text-cream' : 'text-cream-faint'">Pre-order &amp; Pay</span>
                </div>
            </div>

            @if (session('error'))
                <div class="mb-5 rounded-lg border border-rosewood-border bg-rosewood-bg/60 px-4 py-3 text-sm text-rosewood-text">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-rosewood-border bg-rosewood-bg/60 px-4 py-3 text-sm text-rosewood-text">
                    <ul class="list-inside list-disc space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- ============ STEP 1: Details + Table ============ --}}
            <div x-show="step === 1" x-cloak class="space-y-6">
                <section class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5 sm:p-6">
                    <h2 class="mb-5 text-sm font-semibold uppercase tracking-wide text-ember">Your Details</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-cream-muted">Full Name</label>
                            <input name="name" x-model="name" required placeholder="e.g. Ahmad bin Ali"
                                   class="w-full rounded-lg border border-espresso-700 bg-espresso-900 px-3.5 py-2.5 text-sm text-cream placeholder-cream-faint transition focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/20" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-cream-muted">Phone Number</label>
                            <input name="phone_number" x-model="phone" required inputmode="tel" placeholder="01153096070"
                                   class="w-full rounded-lg border border-espresso-700 bg-espresso-900 px-3.5 py-2.5 text-sm text-cream placeholder-cream-faint transition focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/20" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-cream-muted">Email <span class="text-cream-faint">(optional)</span></label>
                            <input name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com"
                                   class="w-full rounded-lg border border-espresso-700 bg-espresso-900 px-3.5 py-2.5 text-sm text-cream placeholder-cream-faint transition focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/20" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-cream-muted">Reservation Date</label>
                            <div class="relative">
                                <select name="reservation_date" x-model="date" required
                                        class="w-full appearance-none rounded-lg border border-espresso-700 bg-espresso-900 px-3.5 py-2.5 pr-10 text-sm text-cream transition focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/20">
                                    <template x-for="d in dateOptions" :key="d.value">
                                        <option :value="d.value" x-text="d.label"></option>
                                    </template>
                                </select>
                                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-cream-faint" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <div x-data="{ open: false }" @click.outside="open = false; syncTimeQuery()">
                            <div class="mb-1.5 flex items-baseline justify-between">
                                <label class="block text-xs font-medium text-cream-muted">Arrival Time</label>
                                <span class="text-[10px] text-cream-faint">8AM–10PM</span>
                            </div>
                            <div class="relative">
                                <input type="hidden" name="arrival_time" :value="time">
                                <input type="text" x-model="timeQuery" autocomplete="off" placeholder="Type or pick a time"
                                       @focus="open = true" @click="open = true" @input="open = true"
                                       @keydown.escape="open = false; syncTimeQuery()"
                                       @keydown.enter.prevent="pickFirstTimeMatch()"
                                       @keydown.down.prevent="open = true"
                                       class="w-full rounded-lg border border-espresso-700 bg-espresso-900 px-3.5 py-2.5 pr-10 text-sm text-cream placeholder-cream-faint transition focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/20" />
                                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-cream-faint" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/></svg>

                                <div x-show="open" x-cloak x-transition.opacity.duration.100ms
                                     class="absolute z-20 mt-1.5 max-h-52 w-full overflow-y-auto rounded-lg border border-espresso-700 bg-espresso-900 py-1 shadow-xl">
                                    <template x-for="t in filteredTimeOptions" :key="t.value">
                                        <button type="button" @click="pickTime(t); open = false"
                                            class="block w-full px-3.5 py-2 text-left text-sm transition"
                                            :class="time === t.value ? 'bg-ember font-semibold text-espresso-950' : 'text-cream hover:bg-espresso-800'"
                                            x-text="t.label"></button>
                                    </template>
                                    <p x-show="filteredTimeOptions.length === 0" class="px-3.5 py-3 text-xs text-cream-faint">No match — try a time between 8:00 AM and 10:00 PM.</p>
                                </div>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-cream-muted">Number of Guests</label>
                            <div class="inline-flex items-center gap-3 rounded-lg border border-espresso-700 bg-espresso-900 p-1.5">
                                <button type="button" @click="decPax()" aria-label="Fewer guests" class="flex h-8 w-8 items-center justify-center rounded-md text-cream-muted transition hover:bg-espresso-800">&minus;</button>
                                <input name="pax" type="number" min="1" max="6" x-model.number="pax" @input="validateSelection()" required
                                       class="w-12 border-0 bg-transparent p-0 text-center text-base font-semibold text-cream focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none" />
                                <button type="button" @click="incPax()" aria-label="More guests" class="flex h-8 w-8 items-center justify-center rounded-md text-cream-muted transition hover:bg-espresso-800">+</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5 sm:p-6">
                    <div class="mb-1 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ember">Select a Table</h2>
                        <span class="text-xs text-cream-faint">Party of <span class="font-semibold text-cream-muted" x-text="pax"></span></span>
                    </div>
                    <p class="mb-4 text-xs text-cream-faint">Green tables can seat your party — tap to select.</p>

                    {{-- Table map --}}
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        <template x-for="t in tables" :key="t.table_id">
                            <button type="button" @click="selectTable(t)" :disabled="!isSelectable(t)"
                                :class="tileClass(t)"
                                class="relative rounded-2xl border p-3 transition focus:outline-none sm:p-4">

                                {{-- selected tick --}}
                                <span x-show="selectedTable === t.table_id" x-cloak
                                      class="absolute right-2.5 top-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-espresso-950/70">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>

                                <p class="text-left font-sans text-lg font-bold leading-none tabular-nums">
                                    T<span x-text="t.table_number"></span>
                                </p>

                                {{-- table + person icons (layout matches capacity) --}}
                                <svg viewBox="0 0 120 120" class="mx-auto h-20 w-20 sm:h-24 sm:w-24" fill="none">
                                    {{-- the table --}}
                                    <rect x="34" y="40" width="52" height="40" rx="10"
                                          stroke="currentColor" stroke-width="4"
                                          fill="currentColor" fill-opacity="0.12" />

                                    {{-- 2 pax: facing each other --}}
                                    <g x-show="t.capacity <= 2" fill="currentColor">
                                        <g transform="translate(60,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                        <g transform="translate(60,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                    </g>

                                    {{-- 4 pax: two top, two bottom --}}
                                    <g x-show="t.capacity > 2 && t.capacity <= 4" fill="currentColor">
                                        <g transform="translate(44,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                        <g transform="translate(76,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                        <g transform="translate(44,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                        <g transform="translate(76,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                    </g>

                                    {{-- 6 pax: two top, two bottom, one each side --}}
                                    <g x-show="t.capacity > 4" fill="currentColor">
                                        <g transform="translate(44,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                        <g transform="translate(76,24)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                        <g transform="translate(15,62)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                        <g transform="translate(105,62)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                        <g transform="translate(44,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                        <g transform="translate(76,100)"><circle cy="-5" r="5.5"/><path d="M-8 12a8 8 0 0 1 16 0Z"/></g>
                                    </g>
                                </svg>

                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-[11px] font-medium opacity-80" x-text="t.capacity + ' pax'"></span>
                                    <span class="text-[10px] font-semibold uppercase tracking-wide" x-text="statusLabel(t)"></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    {{-- Legend --}}
                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1.5 border-t border-espresso-700 pt-3 text-xs text-cream-muted">
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded bg-emerald-500/70"></span> Available</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded bg-ember"></span> Selected</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded bg-rosewood-border"></span> Booked</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded bg-espresso-700"></span> Too small</span>
                    </div>
                </section>

                <p x-show="err" x-cloak class="text-center text-sm text-rosewood-text" x-text="err"></p>
                <button type="button" @click="next()"
                    class="w-full rounded-lg bg-ember py-3.5 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600">
                    Continue to Pre-order &rarr;
                </button>
            </div>

            {{-- ============ STEP 2: Menu + Pay ============ --}}
            <div x-show="step === 2" x-cloak class="space-y-6">
                {{-- Booking recap --}}
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 rounded-xl border border-espresso-700 bg-espresso-900 px-4 py-3 text-sm">
                    <span class="text-cream-muted">Table <span class="font-semibold text-cream" x-text="selectedTableObj?.table_number"></span></span>
                    <span class="text-cream-faint">·</span>
                    <span class="text-cream-muted" x-text="prettyDate"></span>
                    <span class="text-cream-faint">·</span>
                    <span class="text-cream-muted" x-text="prettyTime"></span>
                    <span class="text-cream-faint">·</span>
                    <span class="text-cream-muted"><span x-text="pax"></span> pax</span>
                    <button type="button" @click="back()" class="ml-auto text-xs font-medium text-ember hover:underline">Edit</button>
                </div>

                <section class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5 sm:p-6">
                    <h2 class="mb-5 text-sm font-semibold uppercase tracking-wide text-ember">Pre-order Menu</h2>
                    <div class="space-y-6">
                        @foreach ($menu as $category => $items)
                            <div>
                                <p class="mb-2.5 text-xs font-semibold uppercase tracking-wide text-cream-faint">{{ $category }}</p>
                                <ul class="space-y-1">
                                    @foreach ($items as $item)
                                        <li class="flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition hover:bg-espresso-900/50">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-cream">{{ $item->name }}</p>
                                                <p class="text-xs text-cream-muted">RM {{ number_format($item->price, 2) }}</p>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-2">
                                                <button type="button" @click="dec({{ $item->menu_id }})" aria-label="Remove one" class="flex h-8 w-8 items-center justify-center rounded-lg border border-espresso-700 text-cream-muted transition hover:bg-espresso-800" :class="(qty[{{ $item->menu_id }}] || 0) === 0 && 'opacity-40'">&minus;</button>
                                                <span class="w-6 text-center text-sm font-semibold" :class="(qty[{{ $item->menu_id }}] || 0) > 0 ? 'text-ember' : 'text-cream'" x-text="qty[{{ $item->menu_id }}] || 0"></span>
                                                <button type="button" @click="inc({{ $item->menu_id }})" aria-label="Add one" class="flex h-8 w-8 items-center justify-center rounded-lg border border-espresso-700 text-cream-muted transition hover:bg-espresso-800">+</button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Summary + pay --}}
                <section class="rounded-2xl border border-espresso-700 bg-espresso-850 p-5 sm:p-6">
                    <template x-if="cart.length === 0">
                        <p class="py-2 text-center text-sm text-cream-faint">Add at least one menu item to continue.</p>
                    </template>
                    <ul class="max-h-56 space-y-2 overflow-y-auto" x-show="cart.length > 0">
                        <template x-for="line in cart" :key="line.menu_id">
                            <li class="flex justify-between gap-2 text-sm">
                                <span class="min-w-0 truncate text-cream-muted"><span x-text="line.quantity"></span>× <span x-text="line.name"></span></span>
                                <span class="shrink-0 text-cream">RM <span x-text="line.subtotal.toFixed(2)"></span></span>
                            </li>
                        </template>
                    </ul>
                    <div class="mt-4 space-y-1.5 border-t border-espresso-700 pt-3 text-sm">
                        <div class="flex justify-between text-cream-muted"><span>Order total</span><span>RM <span x-text="total.toFixed(2)"></span></span></div>
                        <div class="flex justify-between text-base font-bold text-cream"><span>Deposit (50%)</span><span class="text-ember">RM <span x-text="(total * 0.5).toFixed(2)"></span></span></div>
                    </div>

                    <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row">
                        <button type="button" @click="back()"
                            class="rounded-lg border border-espresso-700 py-3 text-sm font-medium text-cream-muted transition hover:bg-espresso-800 sm:w-32">
                            &larr; Back
                        </button>
                        <button type="submit" :disabled="cart.length === 0"
                            class="flex-1 rounded-lg bg-ember py-3 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600 disabled:cursor-not-allowed disabled:opacity-40">
                            Proceed to Payment
                        </button>
                    </div>
                    <p class="mt-2 text-center text-[11px] text-cream-faint">Secure deposit via CHIP</p>
                </section>
            </div>

            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('reservation', () => ({
                        tables: @json($tables),
                        menuFlat: @json($menuFlat),
                        dateOptions: @json($dateOptions),
                        timeOptions: @json($timeOptions),
                        step: 1,
                        err: '',
                        name: @js(old('name', '')),
                        phone: @js(old('phone_number', '')),
                        date: @js(old('reservation_date', $dateOptions[0]['value'])),
                        time: @js(old('arrival_time', '19:00')),
                        timeQuery: '',
                        pax: {{ (int) old('pax', 2) }},
                        selectedTable: null,
                        qty: {},

                        init() {
                            this.syncTimeQuery();
                            // If the server bounced us back with errors, resume on the menu step.
                            @if ($errors->any()) this.step = 2; @endif
                        },

                        // --- Arrival time combobox: type to filter, or pick from the list ---
                        get filteredTimeOptions() {
                            const q = this.timeQuery.trim().toLowerCase();
                            if (!q) return this.timeOptions;
                            const current = this.timeOptions.find(t => t.value === this.time);
                            if (current && current.label.toLowerCase() === q) return this.timeOptions;
                            return this.timeOptions.filter(t => t.label.toLowerCase().includes(q));
                        },
                        pickTime(t) { this.time = t.value; this.timeQuery = t.label; },
                        pickFirstTimeMatch() {
                            if (this.filteredTimeOptions.length > 0) this.pickTime(this.filteredTimeOptions[0]);
                        },
                        syncTimeQuery() {
                            const opt = this.timeOptions.find(t => t.value === this.time);
                            this.timeQuery = opt ? opt.label : '';
                        },

                        incPax() { if (this.pax < 6) this.pax++; this.validateSelection(); },
                        decPax() { if (this.pax > 1) this.pax--; },
                        isSelectable(t) { return t.status === 'Available' && t.capacity >= this.pax; },
                        selectTable(t) { if (this.isSelectable(t)) { this.selectedTable = t.table_id; this.err = ''; } },
                        validateSelection() {
                            const t = this.tables.find(x => x.table_id === this.selectedTable);
                            if (t && !this.isSelectable(t)) this.selectedTable = null;
                        },
                        get selectedTableObj() { return this.tables.find(t => t.table_id === this.selectedTable) || null; },
                        tileClass(t) {
                            if (this.selectedTable === t.table_id) return 'border-ember bg-ember text-espresso-950 ring-2 ring-ember/40';
                            if (t.status !== 'Available') return 'border-rosewood-border/40 bg-rosewood-bg/30 text-rosewood-text/70 cursor-not-allowed';
                            if (t.capacity < this.pax) return 'border-espresso-800 bg-espresso-900/40 text-cream-faint opacity-50 cursor-not-allowed';
                            return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200 hover:border-emerald-400/70 hover:bg-emerald-500/20';
                        },
                        statusLabel(t) {
                            if (this.selectedTable === t.table_id) return 'Selected';
                            if (t.status !== 'Available') return 'Booked';
                            return t.capacity >= this.pax ? 'Available' : 'Too small';
                        },

                        next() {
                            this.err = '';
                            if (!this.name.trim() || !this.phone.trim()) { this.err = 'Please fill in your name and phone number.'; return; }
                            if (!this.date || !this.time) { this.err = 'Please choose a reservation date and arrival time.'; return; }
                            if (!this.selectedTable) { this.err = 'Please select a table.'; return; }
                            this.step = 2;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        },
                        back() { this.step = 1; window.scrollTo({ top: 0, behavior: 'smooth' }); },

                        get prettyDate() {
                            if (!this.date) return '';
                            const d = new Date(this.date + 'T00:00:00');
                            return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                        },
                        get prettyTime() {
                            if (!this.time) return '';
                            const [h, m] = this.time.split(':').map(Number);
                            const ap = h >= 12 ? 'PM' : 'AM';
                            const h12 = ((h + 11) % 12) + 1;
                            return `${h12}:${String(m).padStart(2, '0')} ${ap}`;
                        },

                        inc(id) { this.qty[id] = (this.qty[id] || 0) + 1; },
                        dec(id) { this.qty[id] = Math.max(0, (this.qty[id] || 0) - 1); },
                        get cart() {
                            return this.menuFlat
                                .filter(m => (this.qty[m.menu_id] || 0) > 0)
                                .map(m => ({ menu_id: m.menu_id, name: m.name, quantity: this.qty[m.menu_id], subtotal: m.price * this.qty[m.menu_id] }));
                        },
                        get total() { return this.cart.reduce((s, l) => s + l.subtotal, 0); },
                    }));
                });
            </script>
        </form>
    </div>
</x-public-layout>
