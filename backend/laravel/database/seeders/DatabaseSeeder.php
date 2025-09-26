<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // First seed basic data
            AdminUserSeeder::class,
            BranchSeeder::class,
            
            // Then seed geographical data in order of dependencies
            GovernorateSeeder::class,
            WilayatSeeder::class,
            
            // Finally seed the assignments (requires branches and wilayats)
            WilayatBranchAssignmentSeeder::class,
        ]);
    }
}
