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
        // 外部キー制約を一時的に無効化
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. usersテーブルにemail_verified_atカラムがない場合は追加
        if (!Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }

        // 2. Customersテーブルのデータを取得してusersテーブルに挿入
        $customers = DB::table('customers')->get();
        
        foreach ($customers as $customer) {
            // 同じメールアドレスのユーザーが既に存在するかチェック
            $existingUser = DB::table('users')->where('email', $customer->email)->first();
            
            if (!$existingUser) {
                // 新しいユーザーとして挿入
                $userId = DB::table('users')->insertGetId([
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'email_verified_at' => $customer->email_verified_at,
                    'password' => $customer->password ?? bcrypt('password'), // パスワードがない場合はデフォルト
                    'is_admin' => false, // 顧客は管理者ではない
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                ]);
                
                // 3. Reservationsテーブルのcustomer_idをuser_idに更新
                DB::table('reservations')
                    ->where('customer_id', $customer->id)
                    ->update(['customer_id' => $userId]);
            } else {
                // 既存ユーザーの場合、reservationsをそのユーザーに紐付ける
                DB::table('reservations')
                    ->where('customer_id', $customer->id)
                    ->update(['customer_id' => $existingUser->id]);
            }
        }

        // 外部キー制約を有効化
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ロールバックは複雑になるため、データのバックアップを推奨
        // この処理では実装しない
    }
};
