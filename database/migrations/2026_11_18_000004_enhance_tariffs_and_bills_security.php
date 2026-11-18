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
        Schema::table('tariffs', function (Blueprint $table) {
            if (!Schema::hasColumn('tariffs', 'is_simulation')) {
                $table->boolean('is_simulation')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('tariffs', 'approval_status')) {
                $table->enum('approval_status', ['PROVISIONAL', 'SUBMITTED', 'APPROVED_PDAM'])
                    ->default('PROVISIONAL')
                    ->after('is_simulation');
            }
            if (!Schema::hasColumn('tariffs', 'approved_by')) {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->after('approval_status')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('tariffs', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });

        Schema::table('bills', function (Blueprint $table) {
            if (!Schema::hasColumn('bills', 'bill_type')) {
                $table->enum('bill_type', ['SIMULATION', 'OFFICIAL'])
                    ->default('SIMULATION')
                    ->after('tariff_category');
            }
            if (!Schema::hasColumn('bills', 'is_simulation')) {
                $table->boolean('is_simulation')->default(true)->after('bill_type');
            }
        });

        Schema::table('meter_readings', function (Blueprint $table) {
            if (!Schema::hasColumn('meter_readings', 'is_anomaly')) {
                $table->boolean('is_anomaly')->default(false)->after('status');
            }
            if (!Schema::hasColumn('meter_readings', 'anomaly_reason')) {
                $table->string('anomaly_reason', 255)->nullable()->after('is_anomaly');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            if (Schema::hasColumn('meter_readings', 'anomaly_reason')) {
                $table->dropColumn('anomaly_reason');
            }
            if (Schema::hasColumn('meter_readings', 'is_anomaly')) {
                $table->dropColumn('is_anomaly');
            }
        });

        Schema::table('bills', function (Blueprint $table) {
            if (Schema::hasColumn('bills', 'is_simulation')) {
                $table->dropColumn('is_simulation');
            }
            if (Schema::hasColumn('bills', 'bill_type')) {
                $table->dropColumn('bill_type');
            }
        });

        Schema::table('tariffs', function (Blueprint $table) {
            if (Schema::hasColumn('tariffs', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('tariffs', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('tariffs', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
            if (Schema::hasColumn('tariffs', 'is_simulation')) {
                $table->dropColumn('is_simulation');
            }
        });
    }
};
