<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ランダムな20人の顧客を作成
        Customer::factory()->count(20)->create();
        
        // 追加で、よく使いそうな特定の顧客も作成
        Customer::create([
            'name' => '田中 太郎',
            'email' => 'tanaka@example.com',
            'phone' => '090-1234-5678',
        ]);
        
        Customer::create([
            'name' => '佐藤 花子',
            'email' => 'sato@example.com',
            'phone' => '080-9876-5432',
        ]);
        
        Customer::create([
            'name' => '山田 次郎',
            'email' => 'yamada@example.com',
            'phone' => '070-1111-2222',
        ]);
    }
}
