<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimeSlotPreset;

class TimeSlotPresetSeeder extends Seeder
{
    public function run(): void
    {
        TimeSlotPreset::create([
            'name' => '午前3コマ',
            'description' => '午前中の1時間ずつ3コマ',
            'time_slots' => [
                ['start_time' => '09:00', 'end_time' => '10:00', 'capacity' => 1],
                ['start_time' => '10:00', 'end_time' => '11:00', 'capacity' => 1],
                ['start_time' => '11:00', 'end_time' => '12:00', 'capacity' => 1],
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        TimeSlotPreset::create([
            'name' => '午後3コマ',
            'description' => '午後の1時間ずつ3コマ',
            'time_slots' => [
                ['start_time' => '13:00', 'end_time' => '14:00', 'capacity' => 1],
                ['start_time' => '14:00', 'end_time' => '15:00', 'capacity' => 1],
                ['start_time' => '15:00', 'end_time' => '16:00', 'capacity' => 1],
            ],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        TimeSlotPreset::create([
            'name' => '半日×2',
            'description' => '午前・午後の半日ずつ',
            'time_slots' => [
                ['start_time' => '09:00', 'end_time' => '12:00', 'capacity' => 1],
                ['start_time' => '13:00', 'end_time' => '16:00', 'capacity' => 1],
            ],
            'is_active' => true,
            'sort_order' => 3,
        ]);

        TimeSlotPreset::create([
            'name' => '90分×2',
            'description' => '90分間隔の2コマ',
            'time_slots' => [
                ['start_time' => '10:00', 'end_time' => '11:30', 'capacity' => 2],
                ['start_time' => '14:00', 'end_time' => '15:30', 'capacity' => 2],
            ],
            'is_active' => true,
            'sort_order' => 4,
        ]);
    }
}
