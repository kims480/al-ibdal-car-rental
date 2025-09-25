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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_location');
            $table->dateTime('rental_start');
            $table->dateTime('rental_end');
            $table->foreignId('car_id')->nullable()->constrained();
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->foreignId('partner_id')->nullable()->constrained();
            $table->enum('status', ['requested', 'assigned', 'picked_up', 'returned', 'completed'])->default('requested');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
