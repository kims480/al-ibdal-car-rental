<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->unique();
            $table->string('name_ar')->unique();
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert all Oman governorates
        DB::table('governorates')->insert([
            [
                'name_en' => 'Muscat',
                'name_ar' => 'مسقط',
                'code' => 'MSC',
                'description' => 'Capital governorate of Oman',
                'latitude' => 23.5859,
                'longitude' => 58.4059,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'Dhofar',
                'name_ar' => 'ظفار',
                'code' => 'DHF',
                'description' => 'Southern governorate known for Salalah',
                'latitude' => 17.0150,
                'longitude' => 54.0924,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'Musandam',
                'name_ar' => 'مسندم',
                'code' => 'MSD',
                'description' => 'Northern exclave governorate',
                'latitude' => 26.2043,
                'longitude' => 56.2708,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'Al Buraimi',
                'name_ar' => 'البريمي',
                'code' => 'ALB',
                'description' => 'Northwestern governorate',
                'latitude' => 24.2382,
                'longitude' => 55.7938,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'Ad Dakhiliyah',
                'name_ar' => 'الداخلية',
                'code' => 'DAK',
                'description' => 'Interior region with Nizwa as capital',
                'latitude' => 22.9333,
                'longitude' => 57.5333,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'North Al Batinah',
                'name_ar' => 'شمال الباطنة',
                'code' => 'NBA',
                'description' => 'Northern coastal region',
                'latitude' => 24.3667,
                'longitude' => 56.7167,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'South Al Batinah',
                'name_ar' => 'جنوب الباطنة',
                'code' => 'SBA',
                'description' => 'Southern coastal region',
                'latitude' => 23.5167,
                'longitude' => 57.7167,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'North Al Sharqiyah',
                'name_ar' => 'شمال الشرقية',
                'code' => 'NSH',
                'description' => 'Northern eastern region',
                'latitude' => 22.6833,
                'longitude' => 58.5833,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'South Al Sharqiyah',
                'name_ar' => 'جنوب الشرقية',
                'code' => 'SSH',
                'description' => 'Southern eastern region',
                'latitude' => 21.5167,
                'longitude' => 58.9167,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'Ad Dhahirah',
                'name_ar' => 'الظاهرة',
                'code' => 'DHA',
                'description' => 'Western interior region',
                'latitude' => 23.3167,
                'longitude' => 56.7167,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en' => 'Al Wusta',
                'name_ar' => 'الوسطى',
                'code' => 'WST',
                'description' => 'Central desert region',
                'latitude' => 20.1167,
                'longitude' => 56.5167,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};
