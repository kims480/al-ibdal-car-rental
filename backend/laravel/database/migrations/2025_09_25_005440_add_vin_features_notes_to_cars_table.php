<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
                $table->string('vin')->nullable()->after('registration');
                $table->text('features')->nullable()->after('daily_rate');
                $table->text('notes')->nullable()->after('features');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
                $table->dropColumn(['vin', 'features', 'notes']);
        });
    }
};
