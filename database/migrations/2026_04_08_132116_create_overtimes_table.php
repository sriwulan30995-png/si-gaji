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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->date('date');
            $table->timestamp('clock_in')->nullable();  // Jam mulai lembur
            $table->timestamp('clock_out')->nullable(); // Jam selesai lembur
            $table->integer('duration_hours');
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};