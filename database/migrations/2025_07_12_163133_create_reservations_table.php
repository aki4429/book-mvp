<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // ────────── ▼ ここから追加 ▼ ──────────
            $table->foreignId('customer_id')
                  ->constrained()                  // → customers.id 参照（ON DELETE CASCADE）
                  ->cascadeOnDelete();             // Laravel 10 以降は省略可

            $table->foreignId('time_slot_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('status', ['pending', 'confirmed', 'canceled', 'completed'])
                  ->default('pending');

            $table->text('notes')->nullable();     // 管理メモ

            $table->timestamp('reminded_at')->nullable();  // 前日リマインド送信済み判定

            $table->foreignId('created_by')        // 管理者ユーザー
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            // ────────── ▲ ここまで追加 ▲ ──────────

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
