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
        if (!Schema::hasTable('bills')) {
            Schema::create('bills', function (Blueprint $table) {
                $table->id();
                $table->string('bill_number', 60)->unique();
                $table->foreignId('meter_reading_id')->unique()->constrained('meter_readings')->restrictOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('customer_id', 50);
                $table->string('meter_number', 50);
                $table->string('billing_period', 7); // Format: 'YYYY-MM'
                $table->string('period_label', 60);  // e.g. 'November 2026'
                $table->string('tariff_category', 100);
                $table->decimal('previous_reading', 10, 2);
                $table->decimal('current_reading', 10, 2);
                $table->decimal('consumption', 10, 2);
                $table->decimal('water_charge', 12, 2)->default(0.00);
                $table->decimal('admin_fee', 12, 2)->default(0.00);
                $table->decimal('maintenance_fee', 12, 2)->default(0.00);
                $table->decimal('penalty_fee', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->date('due_date');
                $table->enum('status', ['UNPAID', 'PENDING_VERIFICATION', 'PAID', 'CANCELLED'])->default('UNPAID');
                $table->json('tariff_snapshot')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('billing_period');
                $table->index('due_date');
            });
        }

        if (!Schema::hasTable('bill_details')) {
            Schema::create('bill_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
                $table->enum('item_type', ['WATER_USAGE', 'ADMIN_FEE', 'MAINTENANCE_FEE', 'PENALTY', 'DISCOUNT'])->default('WATER_USAGE');
                $table->string('description', 255);
                $table->decimal('volume', 10, 2)->default(0.00);
                $table->decimal('rate', 12, 2)->default(0.00);
                $table->decimal('subtotal', 12, 2)->default(0.00);
                $table->timestamps();

                $table->index(['bill_id', 'item_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_details');
        Schema::dropIfExists('bills');
    }
};
