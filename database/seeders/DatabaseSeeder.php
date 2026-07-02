<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\TableInfo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the initial staff accounts,
     * restaurant tables, and menu items for Asam Pedas Claypot Pantai Kelanang.
     */
    public function run(): void
    {
        // --- Staff accounts (FR-10) — one account per role, password: "password" ---
        $staff = [
            ['username' => 'owner',   'full_name' => 'Siti Hanum binti Ady Santahak', 'role' => 'Owner',        'phone_number' => '0123456789'],
            ['username' => 'waiter',  'full_name' => 'Ahmad Faiz',                     'role' => 'Waiter',       'phone_number' => '0123456781'],
            ['username' => 'cashier', 'full_name' => 'Lim Wei Ling',                   'role' => 'Cashier',      'phone_number' => '0123456783'],
            ['username' => 'kitchen', 'full_name' => 'Raj Kumar',                      'role' => 'Kitchen Staff','phone_number' => '0123456784'],
        ];

        foreach ($staff as $s) {
            User::create([
                'username' => $s['username'],
                'password' => Hash::make('password'),
                'full_name' => $s['full_name'],
                'role' => $s['role'],
                'phone_number' => $s['phone_number'],
            ]);
        }

        // --- Restaurant tables (FR-02) — 7 tables, maximum 6 pax per table ---
        $tables = [
            [1, 2], [2, 2], [3, 4], [4, 4], [5, 4],
            [6, 6], [7, 6],
        ];

        foreach ($tables as [$number, $capacity]) {
            TableInfo::create([
                'table_number' => $number,
                'capacity' => $capacity,
                'status' => 'Available',
            ]);
        }

        // --- Menu items (FR-04, FR-07) ---
        $menu = [
            ['Asam Pedas Claypot Ikan Pari', 'Signature stingray asam pedas in claypot', 18.00, 'Main Dish'],
            ['Asam Pedas Claypot Ayam',      'Chicken asam pedas in claypot',            14.00, 'Main Dish'],
            ['Asam Pedas Claypot Udang',     'Prawn asam pedas in claypot',              22.00, 'Main Dish'],
            ['Nasi Putih',                   'Steamed white rice',                        2.00, 'Side'],
            ['Telur Dadar',                  'Malaysian-style omelette',                  5.00, 'Side'],
            ['Sayur Campur',                 'Mixed stir-fried vegetables',               8.00, 'Side'],
            ['Teh O Ais',                    'Iced black tea',                            2.50, 'Drink'],
            ['Sirap Bandung',                'Rose syrup with milk',                      3.00, 'Drink'],
            ['Air Suam',                     'Warm plain water',                          1.00, 'Drink'],
            ['Cendol',                       'Shaved ice with pandan jelly & gula melaka',5.00, 'Dessert'],
            ['Pulut Mangga',                 'Mango sticky rice',                         7.00, 'Dessert'],
        ];

        foreach ($menu as [$name, $desc, $price, $category]) {
            MenuItem::create([
                'name' => $name,
                'description' => $desc,
                'price' => $price,
                'category' => $category,
                'availability' => true,
            ]);
        }
    }
}
