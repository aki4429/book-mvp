<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();

       // ここから追加 ↓
        $table->date('slot_date');                     // 例: 2025-07-10
        $table->time('start_time');                    // 10:00
        $table->time('end_time');                      // 10:30
        $table->unsignedTinyInteger('capacity')->default(1);
        $table->unsignedBigInteger('service_id')->nullable(); // メニュー連携 (不要なら削除)

        // 同一枠の重複を防ぐ
        $table->unique(['slot_date', 'start_time', 'service_id']);
        // ここまで追加 ↑

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};