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
        Schema::table('cars', function (Blueprint $table) {
            // First, convert existing text features to JSON format
            DB::statement('UPDATE cars SET features = JSON_ARRAY(features) WHERE features IS NOT NULL AND features != ""');
            DB::statement('UPDATE cars SET features = JSON_ARRAY() WHERE features IS NULL OR features = ""');
            
            // Change column type to JSON
            $table->json('features')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            // Convert back to text
            $table->text('features')->nullable()->change();
        });
    }
};
