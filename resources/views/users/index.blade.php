<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">User Management</h2>
            <button @click="$dispatch('open-user', { mode: 'create' })"
                    class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900">
                + Add Staff
            </button>
        </div>
    </x-slot>

    <div class="py-8" x-data="userManager()"
         x-init="@if ($errors->any()) openCreate(@js(old())) @endif">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Flash --}}
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Active staff --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Username</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Phone</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($active as $u)
                            <tr class="text-sm text-gray-700">
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $u->full_name }}</td>
                                <td class="px-5 py-3">{{ $u->username }}</td>
                                <td class="px-5 py-3"><span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">{{ $u->role }}</span></td>
                                <td class="px-5 py-3 text-gray-500">{{ $u->phone_number ?: '—' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click='$dispatch("open-user", { mode: "edit", user: @json($u->only("user_id","username","full_name","role","phone_number")) })'
                                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50">Edit</button>
                                        <form method="POST" action="{{ route('users.deactivate', $u) }}" onsubmit="return confirm('Deactivate {{ $u->username }}?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-600 transition hover:bg-rose-50">Deactivate</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Deactivated staff --}}
            @if ($deactivated->isNotEmpty())
                <div class="mt-6">
                    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-400">Deactivated</h3>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <ul class="divide-y divide-gray-100">
                            @foreach ($deactivated as $u)
                                <li class="flex items-center justify-between px-5 py-3 text-sm">
                                    <span class="text-gray-500">{{ $u->full_name }} <span class="text-gray-400">({{ $u->username }} · {{ $u->role }})</span></span>
                                    <form method="POST" action="{{ route('users.reactivate', $u->user_id) }}">
                                        @csrf
                                        <button class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50">Reactivate</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        {{-- Add / Edit modal --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
             @open-user.window="openFrom($event.detail)" @keydown.escape.window="open = false">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl" @click.outside="open = false">
                <h3 class="mb-4 text-lg font-semibold text-gray-800" x-text="mode === 'edit' ? 'Edit Staff' : 'Add Staff'"></h3>

                <form :action="mode === 'edit' ? '/users/' + form.id : '{{ route('users.store') }}'" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-600">Full Name</label>
                        <input name="full_name" x-model="form.full_name" required class="w-full rounded-lg border-gray-300 text-sm focus:border-gray-400 focus:ring-0" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-600">Username</label>
                        <input name="username" x-model="form.username" required class="w-full rounded-lg border-gray-300 text-sm focus:border-gray-400 focus:ring-0" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-600">Role</label>
                        <select name="role" x-model="form.role" class="w-full rounded-lg border-gray-300 text-sm focus:border-gray-400 focus:ring-0">
                            @foreach ($roles as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-600">Phone Number</label>
                        <input name="phone_number" x-model="form.phone_number" class="w-full rounded-lg border-gray-300 text-sm focus:border-gray-400 focus:ring-0" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-600">
                            Password <span x-show="mode === 'edit'" class="text-gray-400">(leave blank to keep)</span>
                        </label>
                        <input name="password" type="password" x-model="form.password" :required="mode === 'create'" class="w-full rounded-lg border-gray-300 text-sm focus:border-gray-400 focus:ring-0" />
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900" x-text="mode === 'edit' ? 'Save Changes' : 'Create Account'"></button>
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
                    } else {
                        this.openCreate();
                        return;
                    }
                    this.open = true;
                },
            }));
        });
    </script>
</x-app-layout>
