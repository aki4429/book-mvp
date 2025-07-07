<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;
use App\Models\TimeSlot;
use App\Models\User; // 管理者

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
                     // 依存モデルを factory で生成
            'customer_id'  => Customer::factory(),
            'time_slot_id' => TimeSlot::factory(),
            'created_by'   => User::factory(),

            // そのほかの属性
            'status'       => $this->faker->randomElement(['pending','confirmed','canceled']),
            'notes'        => $this->faker->optional()->sentence(),
            'reminded_at'  => null,
        ];
    }
}
