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
        Schema::create('wilayat_branch_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilayat_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure one wilayat can't have multiple primary branches
            $table->unique(['wilayat_id', 'is_primary']);
            $table->index(['wilayat_id', 'is_active']);
            $table->index(['branch_id', 'is_active']);
        });

        // Add some default assignments based on geographical proximity
        // This will be populated later via admin interface or can be set as defaults
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayat_branch_assignments');
    }
};
