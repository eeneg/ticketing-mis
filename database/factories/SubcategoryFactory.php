<?php

namespace Database\Factories;

use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subcategory>
 */
class SubcategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Subcategory::class;

    public function definition(): array
    {
        $subcat_names = [
            'Blue Screen of Death',
            'Printer Jam',
            'Network Connectivity Issues',
            'Software Installation Error',
            'Slow Computer Performance',
            'Email Delivery Failure',
            'User Account Lockout',
            'Malware Infection',
            'VPN Connection Failure',
            'Data Backup Failure',
            'Server Downtime',
            'Hard Drive Failure',
            'Password Reset Request',
            'Peripheral Device Not Recognized',
            'Unresponsive Application',
            'Firewall Configuration Error',
            'File Corruption',
            'Printer Offline',
            'Login Authentication Failure',
            'Security Patch Update Required',
        ];

        return [
            //name, category_id
            'name' => $this->faker->randomElement($subcat_names),
        ];
    }
}
