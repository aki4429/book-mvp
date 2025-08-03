<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 外部キー制約を無効化
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('reservations', function (Blueprint $table) {
            // 既存の外部キー制約を削除
            $table->dropForeign(['customer_id']);
            
            // 新しい外部キー制約を追加 (usersテーブルを参照)
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 外部キー制約を有効化
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 外部キー制約を無効化
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('reservations', function (Blueprint $table) {
            // usersテーブルへの外部キー制約を削除
            $table->dropForeign(['customer_id']);
            
            // customersテーブルへの外部キー制約を復元
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        // 外部キー制約を有効化
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
