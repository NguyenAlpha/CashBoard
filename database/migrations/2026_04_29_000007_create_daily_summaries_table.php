<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->date('summary_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('total_cash', 15, 2)->default(0);
            $table->decimal('total_bank_qr', 15, 2)->default(0);
            $table->decimal('total_wallet', 15, 2)->default(0);
            $table->decimal('total_card', 15, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'summary_date']);
            $table->index(['store_id', 'summary_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
