<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->date();
        $availability_from = $this->faker->dateTimeBetween("$date 08:00", "$date 17:00");
        // 2024-08-16 12:56:00
        $availability_to = $this->faker->dateTimeBetween($availability_from, "$date 17:00");
        $range = [1, 2, 3, 4, 5];

        return [
            /*
            category_id
            subcategory_id
            requestor_id
            remarks
            priority
            difficulty
            target_date
            target_time
            availability_from
            availability_to
            */
            'priority' => $this->faker->randomElement($range),
            'difficulty' => $this->faker->randomElement($range),
            'target_date' => null,
            'target_time' => null,
            // 'date' => $date,
            'remarks' => $this->faker->text(25),
            'availability_from' => $availability_from,
            'availability_to' => $availability_to,
        ];
    }
}
