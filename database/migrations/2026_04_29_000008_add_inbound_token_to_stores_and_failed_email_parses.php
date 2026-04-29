<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm inbound_email_token cho mỗi store — dùng làm định danh webhook
        Schema::table('stores', function (Blueprint $table) {
            $table->string('inbound_email_token', 32)->nullable()->unique()->after('is_active');
        });

        // Lưu email không parse được để owner review thủ công
        Schema::create('failed_email_parses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('from_email')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('fail_reason')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();

            $table->index(['store_id', 'is_resolved']);
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('inbound_email_token');
        });

        Schema::dropIfExists('failed_email_parses');
    }
};
