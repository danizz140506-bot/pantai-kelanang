<?php

namespace Tests\Feature;

use App\Models\TableInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the Table Management module (FR-02 real-time availability,
 * FR-03 digital table assignment) and its role-based access control (FR-10).
 */
class TableManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/tables')->assertRedirect('/login');
    }

    public function test_waiter_can_view_the_floor(): void
    {
        TableInfo::create(['table_number' => 1, 'capacity' => 4, 'status' => 'Available']);

        $this->actingAs($this->staff('Waiter'))
            ->get('/tables')
            ->assertOk()
            ->assertSee('Table Management');
    }

    public function test_kitchen_staff_cannot_access_tables(): void
    {
        $this->actingAs($this->staff('Kitchen Staff'))
            ->get('/tables')
            ->assertForbidden();
    }

    public function test_waiter_can_assign_then_release_a_table(): void
    {
        $table = TableInfo::create(['table_number' => 5, 'capacity' => 2, 'status' => 'Available']);
        $waiter = $this->staff('Waiter');

        $this->actingAs($waiter)
            ->post("/tables/{$table->table_id}/assign")
            ->assertOk()
            ->assertJson(['ok' => true]);
        $this->assertSame('Occupied', $table->fresh()->status);

        // An already-occupied table cannot be assigned again.
        $this->actingAs($waiter)
            ->post("/tables/{$table->table_id}/assign")
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        $this->actingAs($waiter)
            ->post("/tables/{$table->table_id}/release")
            ->assertOk();
        $this->assertSame('Available', $table->fresh()->status);
    }

    public function test_status_endpoint_returns_live_table_data(): void
    {
        TableInfo::create(['table_number' => 9, 'capacity' => 6, 'status' => 'Reserved']);

        $this->actingAs($this->staff('Waiter'))
            ->getJson('/tables/status')
            ->assertOk()
            ->assertJsonFragment(['table_number' => 9, 'status' => 'Reserved']);
    }
}
