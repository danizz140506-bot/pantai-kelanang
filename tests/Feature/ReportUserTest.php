<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\TableInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Feature tests for the Reporting (FR-09) and User Management (FR-10) modules.
 */
class ReportUserTest extends TestCase
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

    private function paidOrder(float $total): Order
    {
        $table = TableInfo::create(['table_number' => 1, 'capacity' => 4, 'status' => 'Occupied']);
        $order = Order::create([
            'table_id' => $table->table_id, 'user_id' => $this->staff('Waiter')->user_id,
            'order_date' => now(), 'status' => 'Served', 'total_amount' => $total,
        ]);
        Payment::create([
            'order_id' => $order->order_id, 'subtotal' => $total, 'discount_amount' => 0,
            'total_amount' => $total, 'payment_method' => 'Cash',
            'payment_status' => 'Successful', 'payment_date' => now(),
        ]);

        return $order;
    }

    // ---- Reporting (FR-09) ----

    public function test_owner_sees_aggregated_daily_revenue(): void
    {
        $this->paidOrder(25.00);
        $this->paidOrder(15.00);

        $this->actingAs($this->staff('Owner'))
            ->get('/reports')
            ->assertOk()
            ->assertSee('RM 40.00');   // total revenue
    }

    public function test_non_owner_cannot_view_reports(): void
    {
        $this->actingAs($this->staff('Cashier'))->get('/reports')->assertForbidden();
    }

    // ---- User Management (FR-10) ----

    public function test_owner_can_create_a_staff_account(): void
    {
        $this->actingAs($this->staff('Owner'))
            ->post('/users', [
                'username' => 'newwaiter',
                'full_name' => 'New Waiter',
                'role' => 'Waiter',
                'phone_number' => '0191234567',
                'password' => 'secret123',
            ])->assertRedirect();

        $this->assertDatabaseHas('users', ['username' => 'newwaiter', 'role' => 'Waiter']);
    }

    public function test_duplicate_username_is_rejected(): void
    {
        $existing = $this->staff('Waiter');

        $this->actingAs($this->staff('Owner'))
            ->post('/users', [
                'username' => $existing->username,
                'full_name' => 'Clone',
                'role' => 'Waiter',
                'password' => 'secret123',
            ])->assertSessionHasErrors('username');
    }

    public function test_waiter_cannot_access_user_management(): void
    {
        $this->actingAs($this->staff('Waiter'))->get('/users')->assertForbidden();
    }

    public function test_owner_can_update_a_staff_account(): void
    {
        $waiter = $this->staff('Waiter');

        $this->actingAs($this->staff('Owner'))
            ->put("/users/{$waiter->user_id}", [
                'username' => $waiter->username,
                'full_name' => 'Renamed Person',
                'role' => 'Cashier',
                'password' => '',
            ])->assertRedirect();

        $this->assertDatabaseHas('users', ['user_id' => $waiter->user_id, 'full_name' => 'Renamed Person', 'role' => 'Cashier']);
    }

    public function test_deactivating_a_user_soft_deletes_and_blocks_login(): void
    {
        $waiter = $this->staff('Waiter');

        $this->actingAs($this->staff('Owner'))
            ->delete("/users/{$waiter->user_id}")
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['user_id' => $waiter->user_id]);
        $this->assertFalse(Auth::attempt(['username' => $waiter->username, 'password' => 'password']));
    }

    public function test_owner_cannot_deactivate_their_own_account(): void
    {
        $owner = $this->staff('Owner');

        $this->actingAs($owner)
            ->delete("/users/{$owner->user_id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['user_id' => $owner->user_id, 'deleted_at' => null]);
    }

    public function test_owner_can_reactivate_a_deactivated_user(): void
    {
        $waiter = $this->staff('Waiter');
        $waiter->delete();

        $this->actingAs($this->staff('Owner'))
            ->post("/users/{$waiter->user_id}/reactivate")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['user_id' => $waiter->user_id, 'deleted_at' => null]);
    }
}
