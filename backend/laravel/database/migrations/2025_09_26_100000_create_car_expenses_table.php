<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('car_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->string('expense_type'); // e.g. maintenance, repair, insurance
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->date('expense_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_expenses');
    }
};
