<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lastNames = ['田中', '佐藤', '鈴木', '高橋', '渡辺', '伊藤', '山本', '中村', '小林', '加藤', '吉田', '山田', '松本', '井上', '木村'];
        $firstNamesM = ['太郎', '次郎', '三郎', '健', '誠', '翔', '大輝', '拓海', '蓮', '颯太'];
        $firstNamesF = ['花子', '美咲', '結衣', '陽菜', '凛', '葵', 'さくら', '美羽', '愛', '心春'];
        
        $lastName = $this->faker->randomElement($lastNames);
        $firstName = $this->faker->randomElement(array_merge($firstNamesM, $firstNamesF));
        
        return [
            'name'  => $lastName . ' ' . $firstName,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->randomElement([
                '090-' . $this->faker->numerify('####-####'),
                '080-' . $this->faker->numerify('####-####'),
                '070-' . $this->faker->numerify('####-####'),
            ]),
        ];
    }
}
