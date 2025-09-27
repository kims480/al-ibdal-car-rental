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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_request_id');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'sent', 'paid', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('issued_by');
            $table->timestamps();
            
            // Foreign keys will be added later when the referenced tables exist
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
