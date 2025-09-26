<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@alibdaltrading.com'],
            [
                'name' => 'AL IBDAL TRADING LLC Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '77307045',
                'city' => 'Muscat',
                'active' => true,
            ]
        );

        // Create a branch manager for Muscat branch
        User::firstOrCreate(
            ['email' => 'manager@alibdaltrading.com'],
            [
                'name' => 'Muscat Branch Manager',
                'password' => Hash::make('password'),
                'role' => 'branch_manager',
                'phone' => '77307046',
                'city' => 'Muscat',
                'branch_id' => 1, // Muscat - Al Khuwair Branch
                'active' => true,
            ]
        );

        // Create a partner user
        User::firstOrCreate(
            ['email' => 'partner@alibdaltrading.com'],
            [
                'name' => 'Partner Staff',
                'password' => Hash::make('password'),
                'role' => 'partner',
                'phone' => '77307047',
                'city' => 'Muscat',
                'branch_id' => 1,
                'active' => true,
            ]
        );
    }
}
