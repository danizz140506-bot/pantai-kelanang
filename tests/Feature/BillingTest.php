<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\TableInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the Billing & Payment module (FR-07 automated bill
 * generation, FR-08 multiple payment methods + table release).
 */
class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role): User
    {
        return User::create([
            'username' => strtolower(str_replace(' ', '', $role)).'_'.uniqid(),
            'password' => 'password',
            'full_name' => $role.' Tester',
            'role' => $role,
            'phone_number' => '0123456789',
        ]);
    }

    private function servedOrder(float $total = 28.00): Order
    {
        $table = TableInfo::create(['table_number' => 3, 'capacity' => 4, 'status' => 'Occupied']);
        $waiter = $this->staff('Waiter');
        $item = MenuItem::create([
            'name' => 'Asam Pedas Claypot Ayam', 'description' => 'x',
            'price' => 14.00, 'category' => 'Main Dish', 'availability' => true,
        ]);
        $order = Order::create([
            'table_id' => $table->table_id, 'user_id' => $waiter->user_id,
            'order_date' => now(), 'status' => 'Served', 'total_amount' => $total,
        ]);
        OrderItem::create([
            'order_id' => $order->order_id, 'menu_id' => $item->menu_id,
            'quantity' => 2, 'subtotal' => 28.00, 'special_instructions' => null,
        ]);

        return $order;
    }

    public function test_cashier_can_view_billing_list(): void
    {
        $this->servedOrder();

        $this->actingAs($this->staff('Cashier'))
            ->get('/billing')
            ->assertOk()
            ->assertSee('Awaiting Payment');
    }

    public function test_waiter_cannot_access_billing(): void
    {
        $this->actingAs($this->staff('Waiter'))
            ->get('/billing')
            ->assertForbidden();
    }

    public function test_cashier_can_settle_a_cash_payment_and_release_the_table(): void
    {
        $order = $this->servedOrder(28.00);

        $this->actingAs($this->staff('Cashier'))
            ->post("/billing/{$order->order_id}", [
                'payment_method' => 'Cash',
                'discount_amount' => 0,
            ])
            ->assertRedirect(route('billing.receipt', $order));

        // Balance = subtotal − discount + SST 6% (28 + 1.68).
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->order_id,
            'payment_method' => 'Cash',
            'payment_status' => 'Successful',
            'total_amount' => 29.68,
        ]);
        $this->assertSame('Available', $order->table->fresh()->status);   // FR-08 release
    }

    public function test_discount_is_applied_and_capped_at_subtotal(): void
    {
        $order = $this->servedOrder(28.00);

        $this->actingAs($this->staff('Cashier'))
            ->post("/billing/{$order->order_id}", [
                'payment_method' => 'Cash',
                'discount_amount' => 1000,   // more than the bill
            ])->assertRedirect();

        $payment = Payment::where('order_id', $order->order_id)->first();
        $this->assertEquals(28.00, (float) $payment->discount_amount);   // capped
        $this->assertEquals(0.00, (float) $payment->total_amount);
    }

    public function test_card_payment_routes_through_the_gateway(): void
    {
        $order = $this->servedOrder(28.00);

        $this->actingAs($this->staff('Cashier'))
            ->post("/billing/{$order->order_id}", [
                'payment_method' => 'Card',
                'discount_amount' => 3.00,
            ])->assertRedirect(route('billing.receipt', $order));

        // Balance = (28 − 3) + SST 6% (25 + 1.50) = 26.50.
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->order_id,
            'payment_method' => 'Card',
            'payment_status' => 'Successful',
            'total_amount' => 26.50,
        ]);
    }

    public function test_qr_payment_is_accepted_and_settled(): void
    {
        $order = $this->servedOrder(28.00);

        $this->actingAs($this->staff('Cashier'))
            ->post("/billing/{$order->order_id}", [
                'payment_method' => 'QR',
                'discount_amount' => 0,
            ])->assertRedirect(route('billing.receipt', $order));

        // QR settles through the gateway (simulated in tests); balance = 28 + SST 6%.
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->order_id,
            'payment_method' => 'QR',
            'payment_status' => 'Successful',
            'total_amount' => 29.68,
        ]);
    }

    public function test_a_settled_order_cannot_be_billed_again(): void
    {
        $order = $this->servedOrder(28.00);
        $cashier = $this->staff('Cashier');

        $this->actingAs($cashier)->post("/billing/{$order->order_id}", ['payment_method' => 'Cash'])->assertRedirect();

        // Second attempt is rejected.
        $this->actingAs($cashier)->get("/billing/{$order->order_id}")->assertNotFound();
        $this->actingAs($cashier)->post("/billing/{$order->order_id}", ['payment_method' => 'Cash'])->assertNotFound();
    }

    public function test_payment_method_is_required_and_validated(): void
    {
        $order = $this->servedOrder();

        $this->actingAs($this->staff('Cashier'))
            ->postJson("/billing/{$order->order_id}", ['payment_method' => 'Bitcoin'])
            ->assertStatus(422);
    }

    public function test_reservation_deposit_is_credited_and_only_the_balance_is_collected(): void
    {
        $order = $this->servedOrder(60.00);            // bill total RM60
        $customer = Customer::create(['name' => 'Ali', 'phone_number' => '0123456789', 'email' => null]);
        Reservation::create([
            'customer_id' => $customer->customer_id,
            'table_id' => $order->table_id,             // same table as the order
            'reservation_date' => now()->toDateString(),
            'arrival_time' => '19:00',
            'pax' => 2,
            'deposit_amount' => 30.00,                  // 50% deposit already paid online
            'deposit_status' => 'Paid',
            'status' => 'Confirmed',
        ]);

        $this->actingAs($this->staff('Cashier'))
            ->post("/billing/{$order->order_id}", ['payment_method' => 'Cash', 'discount_amount' => 0])
            ->assertRedirect(route('billing.receipt', $order));

        // Balance = 60 + SST 6% (3.60) − 30 deposit = 33.60.
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->order_id,
            'subtotal' => 60.00,
            'discount_amount' => 0.00,
            'total_amount' => 33.60,   // balance after deposit
        ]);
        // The reservation is marked Completed once its deposit is consumed.
        $this->assertDatabaseHas('reservations', [
            'table_id' => $order->table_id,
            'status' => 'Completed',
        ]);
    }
}
