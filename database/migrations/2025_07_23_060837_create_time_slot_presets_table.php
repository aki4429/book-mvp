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
        Schema::create('time_slot_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // プリセット名
            $table->json('time_slots'); // 時間枠データ（JSON形式）
            $table->text('description')->nullable(); // 説明
            $table->boolean('is_active')->default(true); // 有効/無効
            $table->integer('sort_order')->default(0); // 表示順序
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slot_presets');
    }
};
