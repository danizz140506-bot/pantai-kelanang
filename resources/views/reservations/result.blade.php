<x-public-layout :title="$ok ? 'Reservation Confirmed' : 'Payment Unsuccessful'">
    <div class="mx-auto max-w-md py-6">
        @if ($ok)
            <div class="rounded-xl border border-espresso-700 bg-espresso-850 p-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/15 ring-1 ring-emerald-500/40">
                    <svg class="h-7 w-7 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="mt-4 font-display text-2xl font-semibold text-cream">Reservation Confirmed</h1>
                <p class="mt-1 text-sm text-cream-muted">Thank you, {{ $reservation->customer->name }}. Your table is booked.</p>

                <div class="mt-6 space-y-2 rounded-lg border border-espresso-700 bg-espresso-900 p-4 text-left text-sm">
                    <div class="flex justify-between"><span class="text-cream-muted">Reservation</span><span class="text-cream">#{{ $reservation->reservation_id }}</span></div>
                    <div class="flex justify-between"><span class="text-cream-muted">Table</span><span class="text-cream">{{ $reservation->table->table_number }}</span></div>
                    <div class="flex justify-between"><span class="text-cream-muted">Date</span><span class="text-cream">{{ \Illuminate\Support\Carbon::parse($reservation->reservation_date)->format('d M Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-cream-muted">Arrival</span><span class="text-cream">{{ \Illuminate\Support\Carbon::parse($reservation->arrival_time)->format('h:i A') }}</span></div>
                    <div class="flex justify-between"><span class="text-cream-muted">Guests</span><span class="text-cream">{{ $reservation->pax }} pax</span></div>
                    <div class="flex justify-between border-t border-espresso-700 pt-2"><span class="text-cream-muted">Deposit paid</span><span class="font-semibold text-ember">RM {{ number_format($reservation->deposit_amount, 2) }}</span></div>
                </div>

                <a href="{{ route('reservations.create') }}" class="mt-6 inline-block rounded-lg border border-espresso-700 px-5 py-2.5 text-sm font-medium text-cream-muted transition hover:border-ember hover:text-ember">Make another reservation</a>
            </div>
        @else
            <div class="rounded-xl border border-espresso-700 bg-espresso-850 p-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rosewood-bg ring-1 ring-rosewood-border">
                    <svg class="h-7 w-7 text-rosewood-text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h1 class="mt-4 font-display text-2xl font-semibold text-cream">Payment Unsuccessful</h1>
                <p class="mt-1 text-sm text-cream-muted">Your deposit was not completed, so your reservation is left <span class="text-cream">unconfirmed (Pending)</span>. Please try booking again.</p>
                <a href="{{ route('reservations.create') }}" class="mt-6 inline-block rounded-lg bg-ember px-5 py-2.5 text-sm font-semibold text-espresso-950 transition hover:bg-ember-600">Try again</a>
            </div>
        @endif
    </div>
</x-public-layout>
