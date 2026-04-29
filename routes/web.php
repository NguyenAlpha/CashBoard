<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ─── Guest ───────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ─── Logout ──────────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ─── Onboarding (auth only, trước khi có store) ──────────────────────────────
// Các route này KHÔNG dùng store.access để tránh redirect loop khi chưa có store
Route::middleware('auth')->group(function () {
    Route::get('/stores/create', [\App\Http\Controllers\StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [\App\Http\Controllers\StoreController::class, 'store'])->name('stores.store');
});

// ─── Authenticated + store.access ────────────────────────────────────────────
Route::middleware(['auth', 'store.access'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Stores — edit/update (TASK-03)
    Route::get('/stores/{store}/edit', [\App\Http\Controllers\StoreController::class, 'edit'])->name('stores.edit');
    Route::patch('/stores/{store}', [\App\Http\Controllers\StoreController::class, 'update'])->name('stores.update');

    // Employees (TASK-04)
    // Route::resource('employees', EmployeeController::class)->except('destroy');

    // Shifts (TASK-04)
    // Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    // Route::patch('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');

    // Cash Entry (TASK-05)
    // Route::get('/cash', [CashEntryController::class, 'index'])->name('cash.index');
    // Route::post('/cash', [CashEntryController::class, 'store'])->name('cash.store');
    // Route::delete('/cash/{transaction}', [CashEntryController::class, 'destroy'])->name('cash.destroy');

    // Transactions (TASK-08)
    // Route::resource('transactions', TransactionController::class)->only(['index', 'edit', 'update', 'destroy']);

    // Import (TASK-06)
    // Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    // Route::post('/import', [ImportController::class, 'upload'])->name('import.upload');
    // Route::post('/import/{batch}/map', [ImportController::class, 'map'])->name('import.map');

    // Export (TASK-10)
    // Route::get('/export/transactions', [ExportController::class, 'transactions'])->name('export.transactions');

    // Owner-only areas
    Route::middleware('owner')->group(function () {
        Route::get('/stores', [\App\Http\Controllers\StoreController::class, 'index'])->name('stores.index');
    });
});

// ─── Inbound email webhook (TASK-07) ─────────────────────────────────────────
// Route::post('/api/inbound-email', [InboundEmailController::class, 'receive'])->name('inbound.email');
