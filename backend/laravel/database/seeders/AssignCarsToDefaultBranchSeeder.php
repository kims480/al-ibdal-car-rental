<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Car;
use App\Models\Branch;

class AssignCarsToDefaultBranchSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get the first available branch as default
        $defaultBranch = Branch::where('active', true)->first();
        
        if (!$defaultBranch) {
            $this->command->warn('No active branches found. Creating default branch...');
            
            // Create a default branch if none exists
            $defaultBranch = Branch::create([
                'name' => 'Main Branch',
                'city' => 'Muscat',
                'address' => 'Main Office Location',
                'contact_email' => 'main@ibdal.com',
                'contact_phone' => '+968 9999 0000',
                'active' => true,
            ]);
            
            $this->command->info('Created default branch: ' . $defaultBranch->name);
        }
        
        // Find all cars without branch assignment
        $unassignedCars = Car::whereNull('branch_id')->get();
        
        if ($unassignedCars->count() > 0) {
            $this->command->info('Found ' . $unassignedCars->count() . ' cars without branch assignment.');
            
            // Assign all unassigned cars to the default branch
            Car::whereNull('branch_id')->update(['branch_id' => $defaultBranch->id]);
            
            $this->command->info('Assigned ' . $unassignedCars->count() . ' cars to "' . $defaultBranch->name . '" branch.');
        } else {
            $this->command->info('All cars already have branch assignments.');
        }
        
        // Display current distribution
        $this->displayCarDistribution();
    }
    
    /**
     * Display current car distribution across branches
     */
    private function displayCarDistribution()
    {
        $branches = Branch::with('cars')->get();
        
        $this->command->info("\n=== Car Distribution by Branch ===");
        
        foreach ($branches as $branch) {
            $carCount = $branch->cars->count();
            $this->command->info("• {$branch->name}: {$carCount} cars");
        }
        
        $totalCars = Car::count();
        $this->command->info("\nTotal cars: {$totalCars}");
    }
}