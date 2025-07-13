<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // ① 予約枠の開始時間をランダムに生成（10:00〜17:00 の 30 分刻み）
        $base   = Carbon::createFromTime(10, 0);          // 10:00
        // $rand   = $base->copy()->addMinutes(30 * rand(0, 14)); // 10:00 + n*30min
        $rand   = $base->copy()->addHours(rand(0, 7)); // 10:00 ～ 17:00
        $end    = $rand->copy()->addHour();          // 1時間枠
        // $end    = $rand->copy()->addMinutes(30);          // 30 分後

        return [
            'date'  => $this->faker->dateTimeBetween('-1 week', '+1 week')
                                       ->format('Y-m-d'),
            'start_time' => $rand->format('H:i'),
            'end_time'   => $end->format('H:i'),
            'capacity'   => $this->faker->numberBetween(1, 4),
            'service_id' => null,               // Service テーブル未使用なら null
        ];
    }
}
