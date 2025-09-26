<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Wilayat;
use App\Models\Governorate;

class WilayatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if wilayats already exist to avoid duplicates
        if (Wilayat::count() > 0) {
            $this->command->info('Wilayats already exist, skipping seeder.');
            return;
        }

        // Since the migration already populates wilayats, this seeder is not needed
        // But we'll keep it here for completeness in case migration data needs to be updated
        $this->command->info('Wilayats are populated via migration.');
    }
}