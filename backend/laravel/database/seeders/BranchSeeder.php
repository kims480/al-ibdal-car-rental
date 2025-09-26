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
            // Major branches in key cities
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
                'address' => 'Central Sohar, North Al Batinah, Sultanate of Oman',
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
            [
                'name' => 'Nizwa Branch',
                'city' => 'Nizwa',
                'address' => 'Birkat Al Mouz, Nizwa, Ad Dakhliyah, Sultanate of Oman',
                'contact_email' => 'nizwa@alibdaltrading.com',
                'contact_phone' => '77307049',
                'latitude' => 22.9333,
                'longitude' => 57.5333,
                'active' => true,
            ],
            [
                'name' => 'Sur Branch',
                'city' => 'Sur',
                'address' => 'Industrial Area, Sur, South Al Sharqiyah, Sultanate of Oman',
                'contact_email' => 'sur@alibdaltrading.com',
                'contact_phone' => '77307050',
                'latitude' => 22.5667,
                'longitude' => 59.5289,
                'active' => true,
            ],
            [
                'name' => 'Ibri Branch',
                'city' => 'Ibri',
                'address' => 'Al Ghubrah, Ibri, Ad Dhahirah, Sultanate of Oman',
                'contact_email' => 'ibri@alibdaltrading.com',
                'contact_phone' => '77307051',
                'latitude' => 23.2256,
                'longitude' => 56.5158,
                'active' => true,
            ],
            [
                'name' => 'Khasab Branch',
                'city' => 'Khasab',
                'address' => 'Khasab City Center, Musandam, Sultanate of Oman',
                'contact_email' => 'khasab@alibdaltrading.com',
                'contact_phone' => '77307052',
                'latitude' => 26.2041,
                'longitude' => 56.2447,
                'active' => true,
            ],
            [
                'name' => 'Al Buraimi Branch',
                'city' => 'Al Buraimi',
                'address' => 'Al Buraimi Commercial District, Al Buraimi, Sultanate of Oman',
                'contact_email' => 'buraimi@alibdaltrading.com',
                'contact_phone' => '77307053',
                'latitude' => 24.2488,
                'longitude' => 55.7931,
                'active' => true,
            ],
            [
                'name' => 'Rustaq Branch',
                'city' => 'Rustaq',
                'address' => 'Rustaq Fort Area, South Al Batinah, Sultanate of Oman',
                'contact_email' => 'rustaq@alibdaltrading.com',
                'contact_phone' => '77307054',
                'latitude' => 23.3917,
                'longitude' => 57.4214,
                'active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
