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
        if (!Schema::hasTable('tariffs')) {
            Schema::create('tariffs', function (Blueprint $table) {
                $table->id();
                $table->string('category_code', 50)->unique();
                $table->string('category_name', 150);
                $table->text('description')->nullable();
                $table->decimal('admin_fee', 12, 2)->default(0.00);
                $table->decimal('maintenance_fee', 12, 2)->default(0.00);
                $table->decimal('penalty_flat', 12, 2)->default(0.00);
                $table->decimal('penalty_percentage', 5, 2)->default(0.00);
                $table->unsignedInteger('due_day_of_month')->default(20);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tariff_tiers')) {
            Schema::create('tariff_tiers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tariff_id')->constrained('tariffs')->cascadeOnDelete();
                $table->unsignedInteger('tier_number');
                $table->string('tier_name', 100);
                $table->unsignedInteger('min_consumption')->default(0);
                $table->unsignedInteger('max_consumption')->nullable();
                $table->decimal('rate_per_m3', 12, 2)->default(0.00);
                $table->timestamps();

                $table->index(['tariff_id', 'min_consumption']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariff_tiers');
        Schema::dropIfExists('tariffs');
    }
};
