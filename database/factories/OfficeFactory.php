<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Office::class;

    public function definition(): array
    {
        $offices = ['HR', 'PDRRMO', 'PICTO', 'PGO-OSP', 'PESO',  'PACCO', 'PLO', 'GMDH', 'PGSO', 'PHRMO', 'PGO-PTDPO', 'PSWDO-PPO', 'PVO', 'PENRO', 'PEO'];
        static $incrementingRoomNumber = 1;
        $address = ['Main Capitol', 'Matti Compound', 'SP Area', 'PEO', 'Coliseum'];
        $building = ['Main Capitol Bulding', 'SP Layer Building', 'Lawyer Officer', 'Mechanical Communications Building', 'SEO Building'];

        return [
            // name, address,  building, room,
            // max 15
            'name' => $this->faker->unique()->randomElement($offices),
            'room' => $incrementingRoomNumber++,
            'address' => $this->faker->randomElement($address),
            'building' => $this->faker->randomElement($building),
        ];
    }
}
