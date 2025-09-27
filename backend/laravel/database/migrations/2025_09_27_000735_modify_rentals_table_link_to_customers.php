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
        Schema::table('rentals', function (Blueprint $table) {
            // Remove service_request_id foreign key and column
            $table->dropForeign(['service_request_id']);
            $table->dropColumn('service_request_id');
            
            // Add customer_id foreign key
            $table->foreignId('customer_id')->after('id')->constrained('customers');
            
            // Add additional fields for better rental management
            $table->date('actual_pickup_date')->nullable()->after('return_date');
            $table->date('actual_return_date')->nullable()->after('actual_pickup_date');
            $table->decimal('security_deposit', 10, 2)->default(0)->after('total_amount');
            $table->decimal('additional_charges', 10, 2)->default(0)->after('security_deposit');
            $table->text('pickup_notes')->nullable()->after('notes');
            $table->text('return_notes')->nullable()->after('pickup_notes');
            $table->string('rental_type', 50)->default('daily')->after('return_notes'); // daily, weekly, monthly
            $table->boolean('insurance_included')->default(false)->after('rental_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            // Remove customer_id and additional fields
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'customer_id',
                'actual_pickup_date',
                'actual_return_date',
                'security_deposit',
                'additional_charges',
                'pickup_notes',
                'return_notes',
                'rental_type',
                'insurance_included'
            ]);
            
            // Restore service_request_id
            $table->foreignId('service_request_id')->after('id')->constrained('service_requests');
        });
    }
};
