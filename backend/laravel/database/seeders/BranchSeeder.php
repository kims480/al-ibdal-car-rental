<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Muscat - Al Khuwair Branch',
                'city' => 'Muscat',
                'address' => 'Al Khuwair, Muscat, Sultanate of Oman',
                'contact_email' => 'muscat.khuwair@alibdaltrading.com',
                'contact_phone' => '77307045',
                'latitude' => 23.614328,
                'longitude' => 58.545284,
                'active' => true,
            ],
            [
                'name' => 'Muscat - Qurum Branch',
                'city' => 'Muscat',
                'address' => 'Qurum, Muscat, Sultanate of Oman',
                'contact_email' => 'muscat.qurum@alibdaltrading.com',
                'contact_phone' => '77307046',
                'latitude' => 23.608847,
                'longitude' => 58.490284,
                'active' => true,
            ],
            [
                'name' => 'Sohar Branch',
                'city' => 'Sohar',
                'address' => 'Central Sohar, Sultanate of Oman',
                'contact_email' => 'sohar@alibdaltrading.com',
                'contact_phone' => '77307047',
                'latitude' => 24.341150,
                'longitude' => 56.709661,
                'active' => true,
            ],
            [
                'name' => 'Salalah Branch',
                'city' => 'Salalah',
                'address' => 'Central Salalah, Dhofar, Sultanate of Oman',
                'contact_email' => 'salalah@alibdaltrading.com',
                'contact_phone' => '77307048',
                'latitude' => 17.015476,
                'longitude' => 54.092341,
                'active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
