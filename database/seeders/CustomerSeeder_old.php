<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {        
        // テスト用の顧客アカウントを作成
        User::create([
            'name' => '田中 太郎',
            'email' => 'customer@example.com',
            'phone' => '090-1234-5678',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
        
        User::create([
            'name' => '佐藤 花子',
            'email' => 'sato@example.com',
            'phone' => '080-9876-5432',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
    }
}
        
        Customer::create([
            'name' => '山田 次郎',
            'email' => 'yamada@example.com',
            'phone' => '070-1111-2222',
        ]);
    }
}
