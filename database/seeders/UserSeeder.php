<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'akiyoshi',
            'email' => 'akiyoshi.oda@gmail.com',
            'password' => bcrypt('mb1012'), // 簡易な初期パスワード
        ]);
    }
}
