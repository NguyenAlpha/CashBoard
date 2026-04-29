<?php

use App\Jobs\RecalculateDailySummaryJob;
use App\Models\Store;
use Illuminate\Support\Facades\Schedule;

// Mỗi đêm 00:30: tính lại daily summary cho tất cả stores (safety net)
// Đảm bảo không có inconsistency từ timezone hay failed job trong ngày
Schedule::call(function () {
    $yesterday = now()->subDay()->format('Y-m-d');

    Store::where('is_active', true)->each(function (Store $store) use ($yesterday) {
        RecalculateDailySummaryJob::dispatch($store->id, $yesterday);
    });
})->dailyAt('00:30')->name('nightly-daily-summary')->withoutOverlapping();
