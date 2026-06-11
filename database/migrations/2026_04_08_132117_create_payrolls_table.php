<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->integer('period_month');
            $table->integer('period_year');
            $table->decimal('total_base_salary', 15, 2);
            $table->decimal('total_allowance', 15, 2);
            $table->decimal('total_overtime', 15, 2);
            $table->decimal('total_deduction', 15, 2);
            $table->decimal('net_salary', 15, 2);
            $table->string('status')->default('draft');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
