<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cashier Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    Welcome back, <span class="font-semibold">{{ Auth::user()->full_name }}</span>.
                    You are signed in as <span class="font-semibold">Cashier</span>.
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('billing.index') }}" class="block bg-white shadow-sm sm:rounded-lg p-6 transition hover:shadow-md hover:ring-1 hover:ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Billing &amp; Payment</h3>
                    <p class="text-sm text-gray-500">Generate bills, apply discounts, and process payments (FR-07, FR-08).</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
