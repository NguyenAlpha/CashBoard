<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('import_batch_id')->nullable()->constrained('import_batches')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('source', ['cash', 'bank_qr', 'wallet', 'card']);
            $table->timestamp('transacted_at');
            $table->string('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->json('raw_data')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['store_id', 'transacted_at']);
            $table->index(['store_id', 'source']);
            $table->unique(['store_id', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
