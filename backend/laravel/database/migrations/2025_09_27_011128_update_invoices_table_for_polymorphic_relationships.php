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
        Schema::table('invoices', function (Blueprint $table) {
            // Add polymorphic columns
            $table->string('invoiceable_type')->nullable();
            $table->unsignedBigInteger('invoiceable_id')->nullable();
            
            // Make service_request_id nullable for backward compatibility
            $table->unsignedBigInteger('service_request_id')->nullable()->change();
            
            // Add index for polymorphic relationship
            $table->index(['invoiceable_type', 'invoiceable_id']);
        });
        
        // Migrate existing data to use polymorphic relationship
        DB::table('invoices')
            ->whereNotNull('service_request_id')
            ->update([
                'invoiceable_type' => 'App\\Models\\ServiceRequest',
                'invoiceable_id' => DB::raw('service_request_id')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Remove polymorphic columns
            $table->dropColumn(['invoiceable_type', 'invoiceable_id']);
            $table->dropIndex(['invoiceable_type', 'invoiceable_id']);
            
            // Make service_request_id non-nullable again
            $table->unsignedBigInteger('service_request_id')->nullable(false)->change();
        });
    }
};
