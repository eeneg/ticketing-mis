<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CustomUsersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = User::class;

    public function definition(): array
    {
        $roles = ['admin', 'user', 'user', 'user', 'support', 'support', 'support', 'support', 'officer', 'officer', 'officer', 'officer'];
        $positions = ['PGO-Executivew', 'SP-Legislation', 'SP-Secretariat', 'PGO-Administrative', 'Executive', 'Administrator', 'Chairman'];

        // 12
        return [
            'name' => $this->faker->unique()->name,
            'email' => $this->faker->unique()->safeEmail('@gmail.com'),
            'password' => '12345678',
            'role' => $this->faker->randomElement($roles),
            'number' => $this->faker->phoneNumber(),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'position' => $this->faker->randomElement($positions),

        ];
    }
}
