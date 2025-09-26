<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test partner
        $partner = Partner::create([
            'name' => 'Al Ibdal Test Partner',
            'email' => 'partner@alibdal.com',
            'phone' => '95012345678',
            'branch_id' => null, // No branch assignment initially
            'role' => 'partner',
            'active' => true,
        ]);

        // Create a partner user
        User::create([
            'name' => 'Partner User',
            'email' => 'partner@alibdal.com',
            'phone' => '95012345678',
            'password' => Hash::make('password'),
            'role' => 'partner',
            'active' => true,
        ]);

        // Also create an admin user if it doesn't exist
        if (!User::where('email', 'admin@alibdal.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@alibdal.com',
                'phone' => '95087654321',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'active' => true,
            ]);
        }
    }
}
