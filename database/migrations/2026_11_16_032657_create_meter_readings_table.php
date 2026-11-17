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
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->string('meter_number')->index();
            $table->decimal('reading', 10, 2);
            $table->decimal('previous_reading', 10, 2)->default(0);
            $table->decimal('consumption', 10, 2)->default(0);
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->string('customer_name')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status')->default('Menunggu Validasi');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
