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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'branch_manager', 'partner', 'customer', 'subcontractor'])
                  ->default('customer');
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['role', 'phone', 'city', 'branch_id', 'active']);
        });
    }
};
