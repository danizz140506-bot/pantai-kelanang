<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ChipWebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public, guest-facing online reservation with CHIP deposit (FR-01).
Route::get('/', [ReservationController::class, 'create'])->name('reservations.create');
Route::post('/reserve', [ReservationController::class, 'store'])->name('reservations.store');
Route::get('/reserve/return/{reservation}', [ReservationController::class, 'return'])->name('reservations.return');

// CHIP payment webhook (server-to-server; CSRF-exempt — see bootstrap/app.php).
Route::post('/webhooks/chip', [ChipWebhookController::class, 'handle'])->name('webhooks.chip');

// Role-aware dashboard — dispatches each staff role to their own screen.
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Table Management + Order taking — waiters work the floor; the owner may oversee (FR-02, FR-03, FR-04).
Route::middleware(['auth', 'role:Waiter,Owner'])->group(function () {
    Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
    Route::get('/tables/status', [TableController::class, 'status'])->name('tables.status');
    Route::post('/tables/{table}/assign', [TableController::class, 'assign'])->name('tables.assign');
    Route::post('/tables/{table}/release', [TableController::class, 'release'])->name('tables.release');

    Route::get('/tables/{table}/order', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/feed', [OrderController::class, 'feed'])->name('orders.feed');
});

// Kitchen Display System — kitchen staff view orders and update status (FR-05, FR-06).
Route::middleware(['auth', 'role:Kitchen Staff,Owner'])->group(function () {
    Route::get('/kds', [KitchenController::class, 'index'])->name('kds.index');
    Route::get('/kds/feed', [KitchenController::class, 'feed'])->name('kds.feed');
    Route::post('/orders/{order}/status', [KitchenController::class, 'updateStatus'])->name('orders.status');
});

// Billing & Payment — cashier settles bills (FR-07, FR-08).
Route::middleware(['auth', 'role:Cashier,Owner'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/{order}', [BillingController::class, 'show'])->name('billing.show');
    Route::post('/billing/{order}', [BillingController::class, 'store'])->name('billing.pay');
    Route::get('/billing/{order}/receipt', [BillingController::class, 'receipt'])->name('billing.receipt');
});

// Reporting + User Management — owner only (FR-09, FR-10).
Route::middleware(['auth', 'role:Owner'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/reactivate', [UserController::class, 'reactivate'])
        ->withTrashed()
        ->name('users.reactivate');
});

require __DIR__.'/auth.php';
