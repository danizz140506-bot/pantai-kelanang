<x-guest-layout>
    <div class="relative w-full max-w-[400px] overflow-hidden rounded-[20px] border border-espresso-700 bg-espresso-850 px-8 pb-8 pt-11 shadow-card">

        {{-- Top ember accent --}}
        <span class="absolute left-6 right-6 top-0 h-[3px] rounded-full bg-ember shadow-[0_0_18px_rgba(240,133,31,0.5)]"></span>

        {{-- Logo badge --}}
        <div class="mx-auto h-16 w-16 overflow-hidden rounded-full bg-cream ring-1 ring-ember/50 shadow-[0_0_0_6px_rgba(240,133,31,0.06)]">
            <img src="{{ asset('images/logo-icon.png') }}" alt="Asam Pedas Claypot Pantai Kelanang" class="h-full w-full object-cover" />
        </div>

        {{-- Brand --}}
        <p class="mt-5 text-center text-[11px] font-semibold uppercase tracking-[0.3em] text-ember">
            Restaurant Management
        </p>
        <h1 class="mt-2 text-center font-display text-[30px] font-semibold leading-[1.12] text-cream">
            Asam Pedas Claypot<br>Pantai Kelanang
        </h1>
        <p class="mt-2 text-center text-sm text-cream-muted">
            Sign in to continue to your dashboard
        </p>

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
            @csrf

            {{-- Username --}}
            <div>
                <label for="username" class="mb-2 block text-[13px] font-medium text-cream-muted">Username</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-cream-faint">
                        <svg viewBox="0 0 24 24" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                            <path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" />
                        </svg>
                    </span>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus autocomplete="username"
                        class="w-full rounded-xl border bg-espresso-900 py-3 pl-11 pr-4 text-sm text-cream placeholder-cream-faint transition focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/25 @error('username') border-rosewood-border @else border-espresso-700 @enderror" />
                </div>
            </div>

            {{-- Password --}}
            <div x-data="{ show: false }">
                <label for="password" class="mb-2 block text-[13px] font-medium text-cream-muted">Password</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-cream-faint">
                        <svg viewBox="0 0 24 24" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="10.5" width="16" height="10" rx="2.5" />
                            <path d="M8 10.5V7.5a4 4 0 0 1 8 0v3" />
                        </svg>
                    </span>
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                        class="w-full rounded-xl border bg-espresso-900 py-3 pl-11 pr-11 text-sm text-cream placeholder-cream-faint transition focus:border-ember focus:outline-none focus:ring-2 focus:ring-ember/25 @error('username') border-rosewood-border @else border-espresso-700 @enderror" />
                    <button type="button" @click="show = !show" :aria-label="show ? 'Hide password' : 'Show password'"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-cream-faint transition hover:text-cream-muted focus:text-ember focus:outline-none">
                        <svg x-show="!show" viewBox="0 0 24 24" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
                            <circle cx="12" cy="12" r="2.7" />
                        </svg>
                        <svg x-show="show" x-cloak viewBox="0 0 24 24" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3l18 18" />
                            <path d="M10.6 6.1A9 9 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-3.2 3.7M6.2 7.7A16 16 0 0 0 2.5 12S6 18 12 18a8.6 8.6 0 0 0 3.1-.6" />
                            <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Forgot password --}}
            <div x-data="{ open: false }" class="flex flex-col items-end">
                <button type="button" @click="open = !open" class="text-[13px] text-cream-muted transition hover:text-ember focus:text-ember focus:outline-none">
                    Forgot password?
                </button>
                <p x-show="open" x-cloak x-transition class="mt-1.5 text-right text-[12px] leading-snug text-cream-faint">
                    Please contact the Owner to reset your account password.
                </p>
            </div>

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="flex items-center gap-2.5 rounded-xl border border-rosewood-border bg-rosewood-bg/60 px-4 py-3">
                    <svg viewBox="0 0 24 24" class="h-[18px] w-[18px] shrink-0 text-rosewood-text" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 8v5M12 16h.01" />
                    </svg>
                    <span class="text-[13px] text-rosewood-text">{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Submit --}}
            <button type="submit"
                class="w-full rounded-xl bg-ember py-3 text-sm font-semibold text-espresso-950 transition-colors hover:bg-ember-600 focus:outline-none focus:ring-2 focus:ring-ember/50 focus:ring-offset-2 focus:ring-offset-espresso-850">
                Login
            </button>
        </form>

        {{-- Divider --}}
        <div class="mt-8 flex items-center gap-3">
            <span class="h-px flex-1 bg-espresso-700"></span>
            <span class="text-[10px] font-medium uppercase tracking-[0.25em] text-cream-faint">Pantai Kelanang Branch</span>
            <span class="h-px flex-1 bg-espresso-700"></span>
        </div>

        {{-- Footer --}}
        <p class="mt-4 text-center text-[11px] tracking-wide text-cream-faint">
            Authorized staff access only
        </p>
    </div>
</x-guest-layout>
