<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->enum('source_type', ['csv', 'xlsx', 'email']);
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->integer('row_count')->default(0);
            $table->integer('imported_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->json('column_mapping')->nullable();
            $table->json('error_log')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
