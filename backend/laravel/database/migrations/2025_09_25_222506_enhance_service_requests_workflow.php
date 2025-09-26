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
        Schema::table('service_requests', function (Blueprint $table) {
            // Update status enum to include all workflow states
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM(
                'submitted', 'under_review', 'assigned', 'car_allocated', 
                'customer_notified', 'car_delivered', 'rental_active', 
                'car_returned', 'payment_pending', 'completed', 'cancelled'
            ) DEFAULT 'submitted'");
            
            // Add workflow tracking fields
            $table->foreignId('assigned_branch_id')->nullable()->after('branch_id')->constrained('branches')->onDelete('set null');
            $table->foreignId('allocated_car_id')->nullable()->after('car_id')->constrained('cars')->onDelete('set null');
            $table->foreignId('assignee_id')->nullable()->after('partner_id')->constrained('users')->onDelete('set null');
            
            // Add notification tracking
            $table->timestamp('customer_notified_at')->nullable()->after('notes');
            $table->string('notification_method', 50)->nullable()->after('customer_notified_at'); // email, sms, phone
            
            // Add car delivery tracking
            $table->timestamp('car_delivered_at')->nullable()->after('notification_method');
            $table->timestamp('actual_rental_start')->nullable()->after('car_delivered_at');
            $table->timestamp('actual_rental_end')->nullable()->after('actual_rental_start');
            
            // Add billing information
            $table->decimal('calculated_amount', 10, 2)->nullable()->after('actual_rental_end');
            $table->integer('rental_days')->nullable()->after('calculated_amount');
            $table->decimal('daily_rate_used', 8, 2)->nullable()->after('rental_days');
            
            // Add payment tracking
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'refunded'])->default('pending')->after('daily_rate_used');
            $table->timestamp('payment_received_at')->nullable()->after('payment_status');
            $table->decimal('amount_paid', 10, 2)->nullable()->after('payment_received_at');
            $table->string('payment_method', 50)->nullable()->after('amount_paid');
            
            // Add invoice tracking
            $table->string('invoice_number', 50)->nullable()->after('payment_method');
            $table->enum('invoice_delivery', ['email', 'print', 'both'])->nullable()->after('invoice_number');
            $table->timestamp('invoice_sent_at')->nullable()->after('invoice_delivery');
            
            // Add workflow timestamps
            $table->timestamp('reviewed_at')->nullable()->after('invoice_sent_at');
            $table->timestamp('assigned_at')->nullable()->after('reviewed_at');
            $table->timestamp('completed_at')->nullable()->after('assigned_at');
            
            // Add service type and priority if not exists
            $table->string('service_type', 50)->default('rental')->after('wilayat_id');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('service_type');
            
            // Add proximity flag for HQ decision making
            $table->boolean('is_near_muscat')->default(false)->after('priority');
            $table->decimal('distance_to_nearest_branch', 8, 2)->nullable()->after('is_near_muscat');
            
            // Add indexes for performance
            $table->index(['status', 'created_at']);
            $table->index(['assigned_branch_id', 'status']);
            $table->index(['assignee_id', 'status']);
            $table->index(['payment_status', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // Remove added columns
            $table->dropForeign(['assigned_branch_id']);
            $table->dropForeign(['allocated_car_id']);
            $table->dropForeign(['assignee_id']);
            
            $table->dropColumn([
                'assigned_branch_id', 'allocated_car_id', 'assignee_id',
                'customer_notified_at', 'notification_method',
                'car_delivered_at', 'actual_rental_start', 'actual_rental_end',
                'calculated_amount', 'rental_days', 'daily_rate_used',
                'payment_status', 'payment_received_at', 'amount_paid', 'payment_method',
                'invoice_number', 'invoice_delivery', 'invoice_sent_at',
                'reviewed_at', 'assigned_at', 'completed_at',
                'service_type', 'priority', 'is_near_muscat', 'distance_to_nearest_branch'
            ]);
            
            // Restore original status enum
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM(
                'requested', 'assigned', 'picked_up', 'returned', 'completed'
            ) DEFAULT 'requested'");
        });
    }
};
