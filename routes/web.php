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
// Không dùng store.access để tránh redirect loop khi chưa có store
Route::middleware(['auth', 'owner'])->group(function () {
    Route::get('/stores/create', [\App\Http\Controllers\StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [\App\Http\Controllers\StoreController::class, 'store'])->name('stores.store');
});

// ─── Authenticated + store.access ────────────────────────────────────────────
Route::middleware(['auth', 'store.access'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Stores — edit/update/activate (TASK-03)
    Route::get('/stores/{store}/edit', [\App\Http\Controllers\StoreController::class, 'edit'])->name('stores.edit');
    Route::patch('/stores/{store}', [\App\Http\Controllers\StoreController::class, 'update'])->name('stores.update');
    Route::post('/stores/{store}/activate', [\App\Http\Controllers\StoreController::class, 'activate'])->name('stores.activate');

    // Employees (TASK-04)
    Route::get('/employees', [\App\Http\Controllers\EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [\App\Http\Controllers\EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [\App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [\App\Http\Controllers\EmployeeController::class, 'edit'])->name('employees.edit');
    Route::patch('/employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'update'])->name('employees.update');
    Route::post('/employees/{employee}/toggle', [\App\Http\Controllers\EmployeeController::class, 'toggleActive'])->name('employees.toggle');

    // Shifts (TASK-04)
    Route::post('/shifts/open', [\App\Http\Controllers\ShiftController::class, 'open'])->name('shifts.open');
    Route::patch('/shifts/{shift}/close', [\App\Http\Controllers\ShiftController::class, 'close'])->name('shifts.close');

    // Cash Entry (TASK-05)
    Route::get('/cash', [\App\Http\Controllers\CashEntryController::class, 'index'])->name('cash.index');
    Route::post('/cash', [\App\Http\Controllers\CashEntryController::class, 'store'])->name('cash.store');
    Route::delete('/cash/{transaction}', [\App\Http\Controllers\CashEntryController::class, 'destroy'])->name('cash.destroy');

    // Transactions (TASK-08)
    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');
    Route::delete('/transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Import (TASK-06)
    Route::get('/import', [\App\Http\Controllers\ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [\App\Http\Controllers\ImportController::class, 'upload'])->name('import.upload');
    Route::post('/import/{batch}/map', [\App\Http\Controllers\ImportController::class, 'map'])->name('import.map');

    // Export (TASK-10)
    Route::get('/export', [\App\Http\Controllers\ExportController::class, 'index'])->name('export.index');
    Route::get('/export/transactions', [\App\Http\Controllers\ExportController::class, 'transactions'])->name('export.transactions');
    Route::get('/export/summary', [\App\Http\Controllers\ExportController::class, 'summary'])->name('export.summary');

    // Owner-only areas
    Route::middleware('owner')->group(function () {
        Route::get('/stores', [\App\Http\Controllers\StoreController::class, 'index'])->name('stores.index');
    });

});

// ─── Inbound email webhook (TASK-07) ─────────────────────────────────────────
// CSRF excluded qua bootstrap/app.php validateCsrfTokens(except: [...])
Route::post('/api/inbound-email/{token}', [\App\Http\Controllers\InboundEmailController::class, 'receive'])
    ->name('inbound.email');
