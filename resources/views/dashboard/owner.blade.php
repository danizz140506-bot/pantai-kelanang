<x-admin-layout title="Dashboard">
    <div x-data="userManager()" x-init="@if ($errors->any()) openCreate(@js(old())) @endif">

        {{-- Header --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-2xl font-semibold text-cream sm:text-3xl">Dashboard</h1>
                <p class="text-sm text-cream-muted">Welcome back, {{ auth()->user()->full_name }} · {{ now()->format('l, j M Y') }}</p>
            </div>
            <a href="{{ route('reports.index') }}" class="rounded-lg border border-espresso-700 px-4 py-2 text-sm font-medium text-cream-muted transition hover:border-ember hover:text-ember">
                Full sales report &rarr;
            </a>
        </div>

        {{-- Flash --}}
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg border border-rosewood-border bg-rosewood-bg/40 px-4 py-3 text-sm text-rosewood-text">{{ session('error') }}</div>
        @endif

        {{-- Metric cards --}}
        @php
            $cards = [
                ['label' => "Today's Sales", 'value' => 'RM '.number_format($metrics['sales'], 2), 'icon' => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
                ['label' => "Today's Orders", 'value' => $metrics['orders'], 'icon' => 'M9 5h6M9 3v2M5 7h14l-1 14H6zM9 11v6M15 11v6'],
                ['label' => 'Avg / Order', 'value' => 'RM '.number_format($metrics['avgOrder'], 2), 'icon' => 'M4 19V5m0 14h16M8 15v-4m4 4V9m4 6v-6'],
                ['label' => 'Active Staff', 'value' => $metrics['activeStaff'], 'icon' => 'M17 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6M21 20v-2a4 4 0 0 0-3-3.87M16 4.13A4 4 0 0 1 16 11.6'],
            ];
        @endphp
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($cards as $c)
                <div class="rounded-2xl border border-espresso-700 bg-espresso-850 p-4 sm:p-5">
                    <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-ember/15 text-ember">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}"/></svg>
                    </div>
                    <p class="text-xs font-medium uppercase tracking-wide text-cream-faint">{{ $c['label'] }}</p>
                    <p class="mt-0.5 text-xl font-bold text-cream sm:text-2xl">{{ $c['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Sales chart --}}
        <div class="mt-6 rounded-2xl border border-espresso-700 bg-espresso-850 p-5 sm:p-6">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ember">Daily Sales Overview</h2>
                    <p class="text-xs text-cream-faint">Last 7 days</p>
                </div>
            </div>
            @php $maxRev = max($chart->max('revenue'), 1); @endphp
            <div class="flex h-44 items-stretch justify-between gap-2 sm:gap-4">
                @foreach ($chart as $d)
                    <div class="group flex flex-1 flex-col items-center gap-1.5">
                        <span class="text-[10px] font-semibold text-ember {{ $d['revenue'] > 0 ? '' : 'invisible' }}">{{ number_format($d['revenue'], 0) }}</span>
                        <div class="relative w-full flex-1">
                            <div class="absolute inset-x-0 bottom-0 rounded-t-md bg-ember/80 transition-all group-hover:bg-ember"
                                 style="height: {{ $d['revenue'] > 0 ? max(4, round($d['revenue'] / $maxRev * 100)) : 0 }}%"
                                 title="{{ $d['date'] }}: RM {{ number_format($d['revenue'], 2) }}"></div>
                        </div>
                        <span class="text-[11px] font-medium text-cream-muted">{{ $d['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- User Management --}}
        <div class="mt-6 rounded-2xl border border-espresso-700 bg-espresso-850">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-espresso-700 px-5 py-4">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ember">User Management</h2>
                    <p class="text-xs text-cream-faint">Staff accounts &amp; access</p>
                </div>
                <button @click="openCreate()" class="flex items-center gap-1.5 rounded-lg bg-ember px-4 py-2 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                    Add User
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-espresso-700 text-sm">
                    <thead>
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-cream-faint">
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Username</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Phone</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-espresso-800">
                        @foreach ($active as $u)
                            @php $ud = $u->only('user_id', 'username', 'full_name', 'role', 'phone_number'); @endphp
                            <tr class="text-cream-muted">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-ember/15 text-xs font-bold text-ember">{{ strtoupper(substr($u->full_name, 0, 1)) }}</span>
                                        <span class="font-medium text-cream">{{ $u->full_name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">{{ $u->username }}</td>
                                <td class="px-5 py-3"><span class="rounded-full bg-espresso-800 px-2.5 py-0.5 text-xs font-medium text-cream">{{ $u->role }}</span></td>
                                <td class="px-5 py-3">{{ $u->phone_number ?: '—' }}</td>
                                <td class="px-5 py-3"><span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-xs font-semibold text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Active</span></td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click='openFrom({ mode: "edit", user: @json($ud) })'
                                                class="rounded-lg border border-espresso-700 px-3 py-1.5 text-xs font-medium text-cream-muted transition hover:border-ember hover:text-ember">Edit</button>
                                        <form method="POST" action="{{ route('users.deactivate', $u) }}" onsubmit="return confirm('Deactivate {{ $u->username }}?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-lg border border-rosewood-border/50 px-3 py-1.5 text-xs font-medium text-rosewood-text transition hover:bg-rosewood-bg/40">Deactivate</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Deactivated --}}
            @if ($deactivated->isNotEmpty())
                <div class="border-t border-espresso-700 px-5 py-4">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-cream-faint">Deactivated</p>
                    <ul class="space-y-2">
                        @foreach ($deactivated as $u)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-cream-faint">{{ $u->full_name }} <span class="text-cream-faint/70">({{ $u->username }} · {{ $u->role }})</span></span>
                                <form method="POST" action="{{ route('users.reactivate', $u->user_id) }}">
                                    @csrf
                                    <button class="rounded-lg border border-emerald-500/30 px-3 py-1.5 text-xs font-medium text-emerald-300 transition hover:bg-emerald-500/10">Reactivate</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Add / Edit modal --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="open = false">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-espresso-700 bg-espresso-850 p-6 shadow-2xl">
                <button @click="open = false" class="absolute right-4 top-4 text-cream-faint transition hover:text-cream">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h3 class="font-display text-xl font-semibold text-cream" x-text="mode === 'edit' ? 'Edit Staff Account' : 'Add New User'"></h3>
                <p class="mb-5 text-sm text-cream-muted">Create a staff account with access permissions.</p>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-rosewood-border bg-rosewood-bg/40 px-4 py-3 text-sm text-rosewood-text">
                        <ul class="list-inside list-disc space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form :action="mode === 'edit' ? '/users/' + form.id : '{{ route('users.store') }}'" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-cream-muted">Full Name</label>
                        <input name="full_name" x-model="form.full_name" required placeholder="Full name as per IC"
                               class="w-full rounded-lg border border-espresso-700 bg-espresso-900 px-3.5 py-2.5 text-sm text-cream placeholder-cream-faint focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/20" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-cream-muted">Username</label>
                        <input name="username" x-model="form.username" required placeholder="e.g. cashier01"
                               class="w-full rounded-lg border bg-espresso-900 px-3.5 py-2.5 text-sm text-cream placeholder-cream-faint focus:outline-none focus:ring-2 @error('username') border-rosewood-border focus:border-rosewood-border focus:ring-rosewood-border/20 @else border-espresso-700 focus:border-ember focus:ring-ember/20 @enderror" />
                        @error('username')
                            <p class="mt-1.5 text-xs text-rosewood-text">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-cream-muted">Role</label>
                        <input type="hidden" name="role" :value="form.role">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($roles as $r)
                                <button type="button" @click="form.role = '{{ $r }}'"
                                    class="rounded-lg border px-3 py-2 text-xs font-semibold transition"
                                    :class="form.role === '{{ $r }}' ? 'border-ember bg-ember text-espresso-950' : 'border-espresso-700 bg-espresso-900 text-cream-muted hover:border-ember/60'">
                                    {{ $r }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-cream-muted">Phone Number</label>
                        <input name="phone_number" x-model="form.phone_number" inputmode="tel" placeholder="0123456789"
                               class="w-full rounded-lg border border-espresso-700 bg-espresso-900 px-3.5 py-2.5 text-sm text-cream placeholder-cream-faint focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/20" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-cream-muted">
                            Password <span x-show="mode === 'edit'" class="text-cream-faint">(leave blank to keep)</span>
                        </label>
                        <input name="password" type="password" x-model="form.password" :required="mode === 'create'" placeholder="Minimum 6 characters"
                               class="w-full rounded-lg border border-espresso-700 bg-espresso-900 px-3.5 py-2.5 text-sm text-cream placeholder-cream-faint focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/20" />
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="rounded-lg border border-espresso-700 px-4 py-2 text-sm font-medium text-cream-muted transition hover:bg-espresso-800">Cancel</button>
                        <button type="submit" class="rounded-lg bg-ember px-4 py-2 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600" x-text="mode === 'edit' ? 'Save Changes' : 'Create User'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userManager', () => ({
                open: false,
                mode: 'create',
                form: { id: null, username: '', full_name: '', role: 'Waiter', phone_number: '', password: '' },
                blank() { return { id: null, username: '', full_name: '', role: 'Waiter', phone_number: '', password: '' }; },
                openCreate(old = {}) {
                    this.mode = 'create';
                    this.form = { ...this.blank(), username: old.username || '', full_name: old.full_name || '', role: old.role || 'Waiter', phone_number: old.phone_number || '' };
                    this.open = true;
                },
                openFrom(detail) {
                    if (detail.mode === 'edit' && detail.user) {
                        this.mode = 'edit';
                        this.form = { id: detail.user.user_id, username: detail.user.username, full_name: detail.user.full_name, role: detail.user.role, phone_number: detail.user.phone_number || '', password: '' };
                        this.open = true;
                    } else {
                        this.openCreate();
                    }
                },
            }));
        });
    </script>
</x-admin-layout>
