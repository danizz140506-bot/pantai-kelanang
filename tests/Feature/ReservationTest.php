<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Reservation;
use App\Models\TableInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the public online Reservation module with CHIP deposit
 * (FR-01). CHIP credentials are absent in the test environment, so the
 * redirect-and-verify flow runs in simulation mode (treated as paid).
 */
class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private function menuItem(float $price = 14.00): MenuItem
    {
        return MenuItem::create([
            'name' => 'Asam Pedas Claypot Ayam', 'description' => 'x',
            'price' => $price, 'category' => 'Main Dish', 'availability' => true,
        ]);
    }

    private function table(int $capacity = 4, string $status = 'Available'): TableInfo
    {
        return TableInfo::create(['table_number' => 1, 'capacity' => $capacity, 'status' => $status]);
    }

    private function payload(MenuItem $item, TableInfo $table): array
    {
        return [
            'name' => 'Ali bin Abu',
            'phone_number' => '0123456789',
            'email' => 'ali@example.com',
            'reservation_date' => now()->toDateString(),
            'arrival_time' => '19:00',
            'pax' => 2,
            'table_id' => $table->table_id,   // customer-selected table (SDD 6.2)
            'items' => [['menu_id' => $item->menu_id, 'quantity' => 2]],
        ];
    }

    public function test_guest_can_view_the_reservation_page(): void
    {
        $this->get('/')->assertOk()->assertSee('Reserve a Table');
    }

    public function test_a_paid_reservation_is_confirmed_and_reserves_the_table(): void
    {
        $item = $this->menuItem(14.00);
        $table = $this->table(4);

        $this->followingRedirects()
            ->post('/reserve', $this->payload($item, $table))
            ->assertOk()
            ->assertSee('Reservation Confirmed');

        $this->assertDatabaseHas('customers', ['phone_number' => '0123456789', 'name' => 'Ali bin Abu']);
        $this->assertDatabaseHas('reservations', [
            'table_id' => $table->table_id,
            'pax' => 2,
            'deposit_amount' => 14.00,       // 50% of 2 × 14.00
            'deposit_status' => 'Paid',
            'status' => 'Confirmed',
        ]);
        $this->assertSame('Reserved', $table->fresh()->status);   // FR-02

        // The pre-ordered items are kept with the reservation so the waiter's
        // order screen can pre-fill the cart when the guest arrives.
        $this->assertEquals(
            [['menu_id' => $item->menu_id, 'quantity' => 2]],
            Reservation::first()->preorder_items,
        );
    }

    public function test_reservation_requires_at_least_one_menu_item(): void
    {
        $table = $this->table(4);

        $this->post('/reserve', array_merge($this->payload($this->menuItem(), $table), ['items' => []]))
            ->assertSessionHasErrors('items');
    }

    public function test_selecting_an_unavailable_table_is_rejected(): void
    {
        $item = $this->menuItem();
        $table = $this->table(4, 'Occupied');   // the chosen table is already busy

        $this->post('/reserve', $this->payload($item, $table))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_store_creates_a_pending_reservation_before_payment(): void
    {
        $item = $this->menuItem(14.00);
        $table = $this->table(4);

        // Do not follow the redirect — inspect the state immediately after submission.
        $this->post('/reserve', $this->payload($item, $table))->assertRedirect();

        // SDD 5.2: the RESERVATION record is created up front as Pending, before payment.
        $this->assertDatabaseHas('reservations', [
            'table_id' => $table->table_id,
            'deposit_status' => 'Pending',
            'status' => 'Pending',
        ]);
        // The table is only Reserved once the deposit is paid — still Available here.
        $this->assertSame('Available', $table->fresh()->status);
    }

    public function test_failed_deposit_leaves_the_reservation_pending(): void
    {
        $customer = Customer::create(['name' => 'Ali', 'phone_number' => '0123456789', 'email' => null]);
        $table = $this->table(4);
        $reservation = Reservation::createReservation([
            'customer_id' => $customer->customer_id,
            'table_id' => $table->table_id,
            'reservation_date' => now()->toDateString(),
            'arrival_time' => '19:00',
            'pax' => 2,
            'deposit_amount' => 14.00,
        ]);

        $this->get(route('reservations.return', ['reservation' => $reservation->reservation_id, 'failed' => 1]))
            ->assertOk()
            ->assertSee('Payment Unsuccessful');

        // SDD 5.2 alternative flow: reservation stays Pending, table not reserved.
        $this->assertDatabaseHas('reservations', [
            'reservation_id' => $reservation->reservation_id,
            'deposit_status' => 'Pending',
            'status' => 'Pending',
        ]);
        $this->assertSame('Available', $table->fresh()->status);
    }
}
