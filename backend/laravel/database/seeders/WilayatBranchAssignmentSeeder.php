<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WilayatBranchAssignment;
use App\Models\Wilayat;
use App\Models\Branch;

class WilayatBranchAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if assignments already exist
        if (WilayatBranchAssignment::count() > 0) {
            $this->command->info('Wilayat branch assignments already exist, skipping seeder.');
            return;
        }

        // Get all branches and wilayats
        $branches = Branch::all();
        $wilayats = Wilayat::all();

        if ($branches->count() == 0 || $wilayats->count() == 0) {
            $this->command->error('Branches or Wilayats not found. Please run the database migrations first.');
            return;
        }

        // Simple assignment logic: assign wilayats to the first branch for now
        // This can be updated later with more sophisticated logic
        $assignments = [];
        $primaryBranch = $branches->first();

        foreach ($wilayats as $wilayat) {
            $assignments[] = [
                'wilayat_id' => $wilayat->id,
                'branch_id' => $primaryBranch->id,
                'is_primary' => true,
                'is_active' => true,
                'notes' => 'Default assignment to primary branch',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Create assignments in batches for better performance
        $chunks = array_chunk($assignments, 50);
        foreach ($chunks as $chunk) {
            WilayatBranchAssignment::insert($chunk);
        }

        $this->command->info('Created ' . count($assignments) . ' wilayat-branch assignments.');
    }
}