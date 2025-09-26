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
        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignId('governorate_id')->nullable()->after('customer_location')->constrained()->onDelete('set null');
            $table->foreignId('wilayat_id')->nullable()->after('governorate_id')->constrained()->onDelete('set null');
            $table->index(['governorate_id', 'wilayat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['wilayat_id']);
            $table->dropColumn(['governorate_id', 'wilayat_id']);
        });
    }
};
