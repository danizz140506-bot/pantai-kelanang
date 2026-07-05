<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\TableInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the Order Management + Kitchen Display modules
 * (FR-04 digital order taking, FR-05 KDS, FR-06 order status tracking).
 */
class OrderKdsTest extends TestCase
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

    private function table(string $status = 'Available'): TableInfo
    {
        return TableInfo::create(['table_number' => 7, 'capacity' => 4, 'status' => $status]);
    }

    private function menu(float $price = 14.00, bool $available = true): MenuItem
    {
        return MenuItem::create([
            'name' => 'Asam Pedas Claypot Ayam',
            'description' => 'House special',
            'price' => $price,
            'category' => 'Main Dish',
            'availability' => $available,
        ]);
    }

    public function test_waiter_can_open_the_order_screen(): void
    {
        $this->menu();

        $this->actingAs($this->staff('Waiter'))
            ->get("/tables/{$this->table()->table_id}/order")
            ->assertOk()
            ->assertSee('Take Order');
    }

    public function test_waiter_can_submit_an_order_to_the_kitchen(): void
    {
        $waiter = $this->staff('Waiter');
        $table = $this->table();
        $item = $this->menu(14.00);

        $this->actingAs($waiter)->postJson('/orders', [
            'table_id' => $table->table_id,
            'items' => [
                ['menu_id' => $item->menu_id, 'quantity' => 2, 'special_instructions' => 'less spicy'],
            ],
        ])->assertOk()->assertJson(['ok' => true]);

        $order = Order::first();
        $this->assertSame('Preparing', $order->status);
        $this->assertEquals(28.00, (float) $order->total_amount);     // 2 × 14.00
        $this->assertSame('Occupied', $table->fresh()->status);        // FR-03 link
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->order_id,
            'quantity' => 2,
            'special_instructions' => 'less spicy',
        ]);
    }

    public function test_order_screen_prefills_the_reservation_preorder_for_a_reserved_table(): void
    {
        $waiter = $this->staff('Waiter');
        $item = $this->menu();
        $table = $this->table('Reserved');
        $customer = Customer::create(['name' => 'Ali', 'phone_number' => '0123456789', 'email' => null]);
        Reservation::create([
            'customer_id' => $customer->customer_id,
            'table_id' => $table->table_id,
            'reservation_date' => now()->toDateString(),
            'arrival_time' => '19:00',
            'pax' => 2,
            'deposit_amount' => 14.00,
            'preorder_items' => [['menu_id' => $item->menu_id, 'quantity' => 2]],
            'deposit_status' => 'Paid',
            'status' => 'Confirmed',
        ]);

        // The waiter opens the order screen for the reserved table — the cart is
        // pre-filled with the customer's online pre-order (FR-01).
        $this->actingAs($waiter)
            ->get("/tables/{$table->table_id}/order")
            ->assertOk()
            ->assertViewHas('preorder', [['menu_id' => $item->menu_id, 'quantity' => 2]]);
    }

    public function test_order_screen_has_no_preorder_for_a_walk_in_table(): void
    {
        $this->menu();

        // An Available (walk-in) table has no pre-order to pre-fill.
        $this->actingAs($this->staff('Waiter'))
            ->get("/tables/{$this->table()->table_id}/order")
            ->assertOk()
            ->assertViewHas('preorder', []);
    }

    public function test_order_submission_requires_at_least_one_item(): void
    {
        $this->actingAs($this->staff('Waiter'))
            ->postJson('/orders', ['table_id' => $this->table()->table_id, 'items' => []])
            ->assertStatus(422);
    }

    public function test_kitchen_staff_cannot_take_orders(): void
    {
        $this->actingAs($this->staff('Kitchen Staff'))
            ->get("/tables/{$this->table()->table_id}/order")
            ->assertForbidden();
    }

    public function test_kitchen_can_advance_order_status(): void
    {
        $waiter = $this->staff('Waiter');
        $table = $this->table('Occupied');
        $order = Order::create([
            'table_id' => $table->table_id,
            'user_id' => $waiter->user_id,
            'order_date' => now(),
            'status' => 'Preparing',
            'total_amount' => 14.00,
        ]);

        $kitchen = $this->staff('Kitchen Staff');

        $this->actingAs($kitchen)
            ->postJson("/orders/{$order->order_id}/status", ['status' => 'Ready'])
            ->assertOk()->assertJson(['status' => 'Ready']);
        $this->assertSame('Ready', $order->fresh()->status);

        $this->actingAs($kitchen)
            ->postJson("/orders/{$order->order_id}/status", ['status' => 'Served'])
            ->assertOk();
        $this->assertSame('Served', $order->fresh()->status);
    }

    public function test_waiter_cannot_access_the_kds(): void
    {
        $this->actingAs($this->staff('Waiter'))
            ->get('/kds')
            ->assertForbidden();
    }

    public function test_kds_feed_lists_active_orders_with_items(): void
    {
        $waiter = $this->staff('Waiter');
        $table = $this->table('Occupied');
        $item = $this->menu();
        $order = Order::create([
            'table_id' => $table->table_id,
            'user_id' => $waiter->user_id,
            'order_date' => now(),
            'status' => 'Preparing',
            'total_amount' => 14.00,
        ]);
        OrderItem::create([
            'order_id' => $order->order_id,
            'menu_id' => $item->menu_id,
            'quantity' => 1,
            'subtotal' => 14.00,
            'special_instructions' => null,
        ]);

        $this->actingAs($this->staff('Kitchen Staff'))
            ->getJson('/kds/feed')
            ->assertOk()
            ->assertJsonFragment(['status' => 'Preparing'])
            ->assertJsonFragment(['name' => 'Asam Pedas Claypot Ayam', 'quantity' => 1]);
    }
}
