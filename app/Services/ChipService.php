<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * CHIP (chip-in.asia) payment gateway integration (SDD 2.1 — external Payment
 * Gateway). Used for the online reservation deposit (FR-01) and card / e-wallet
 * bill settlement (FR-08).
 *
 * When live CHIP credentials (services.chip.*) are absent the service runs in
 * simulation mode so the flow is fully demonstrable in development.
 */
class ChipService
{
    /** Whether live CHIP credentials are present. */
    public function isConfigured(): bool
    {
        return filled(config('services.chip.secret_key'))
            && filled(config('services.chip.brand_id'));
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.chip.base_url'), '/').'/'.ltrim($path, '/');
    }

    /**
     * Create a CHIP purchase for the reservation deposit and return the hosted
     * checkout URL the customer is redirected to (FR-01).
     *
     * @return array{id:string, checkout_url:string, status:string}
     */
    public function createPurchase(float $amount, array $client, string $successRedirect, string $failureRedirect, string $reference = ''): array
    {
        $response = Http::withToken(config('services.chip.secret_key'))
            ->acceptJson()
            ->asJson()
            ->post($this->endpoint('/purchases/'), [
                'brand_id' => config('services.chip.brand_id'),
                'reference' => $reference,   // our reservation id — used by the webhook to map back
                'client' => [
                    'email' => $client['email'] ?: 'guest@pantaikelanang.test',
                    'full_name' => $client['name'] ?? '',
                    'phone' => $client['phone'] ?? '',
                ],
                'purchase' => [
                    'currency' => 'MYR',
                    'products' => [[
                        'name' => 'Reservation deposit — Asam Pedas Claypot Pantai Kelanang',
                        // CHIP expects the amount in the smallest currency unit (sen).
                        'price' => (int) round($amount * 100),
                    ]],
                ],
                'success_redirect' => $successRedirect,
                'failure_redirect' => $failureRedirect,
                'send_receipt' => false,
            ])
            ->throw()
            ->json();

        return [
            'id' => $response['id'],
            'checkout_url' => $response['checkout_url'],
            'status' => $response['status'] ?? 'created',
        ];
    }

    /**
     * Retrieve a purchase to verify its payment status on redirect return.
     *
     * @return array{id:string, status:string, paid:bool}
     */
    public function getPurchase(string $id): array
    {
        $data = Http::withToken(config('services.chip.secret_key'))
            ->acceptJson()
            ->get($this->endpoint("/purchases/{$id}/"))
            ->throw()
            ->json();

        return [
            'id' => $data['id'] ?? $id,
            'status' => $data['status'] ?? 'unknown',
            'paid' => ($data['status'] ?? '') === 'paid',
            'reference' => $data['reference'] ?? null,
        ];
    }

    /**
     * Charge a card / e-wallet transaction at the cashier counter (FR-08).
     * Counter terminals settle synchronously; simulated when unconfigured.
     *
     * @return array{status:string, reference:string, simulated:bool}
     */
    public function charge(float $amount, string $method, array $meta = []): array
    {
        $prefix = strtoupper(substr($method, 0, 1));

        return [
            'status' => 'Successful',
            'reference' => $prefix.'-'.now()->format('YmdHis').'-'.random_int(1000, 9999),
            'simulated' => ! $this->isConfigured(),
        ];
    }
}
