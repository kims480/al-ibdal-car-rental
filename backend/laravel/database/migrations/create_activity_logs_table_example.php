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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable(); // e.g., 'rental', 'service', 'invoice'
            $table->text('description'); // Human readable description
            $table->nullableMorphs('subject'); // The model that was acted upon
            $table->nullableMorphs('causer'); // The user who caused this activity
            $table->json('properties')->nullable(); // Store old and new values
            $table->string('event')->nullable(); // created, updated, deleted, etc.
            $table->timestamps();
            
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index(['log_name', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};