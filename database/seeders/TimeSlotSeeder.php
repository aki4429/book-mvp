<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimeSlot;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Log::info('TimeSlotSeeder started');
        TimeSlot::factory()->count(70)->create();
        \Log::info('TimeSlotSeeder completed');
    }
}
