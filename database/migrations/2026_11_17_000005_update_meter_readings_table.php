<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            if (!Schema::hasColumn('meter_readings', 'report_number')) {
                $table->string('report_number')->nullable()->unique();
            }
            if (!Schema::hasColumn('meter_readings', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('meter_readings', 'zone')) {
                $table->string('zone')->nullable();
            }
            if (!Schema::hasColumn('meter_readings', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('meter_readings', 'validated_at')) {
                $table->timestamp('validated_at')->nullable();
            }
            if (!Schema::hasColumn('meter_readings', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (!Schema::hasColumn('meter_readings', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            if (Schema::hasColumn('meter_readings', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (DB::getDriverName() !== 'sqlite') {
                if (Schema::hasColumn('meter_readings', 'user_id')) {
                    $table->dropForeign(['user_id']);
                }
                if (Schema::hasColumn('meter_readings', 'validated_by')) {
                    $table->dropForeign(['validated_by']);
                }
                if (Schema::hasColumn('meter_readings', 'report_number')) {
                    $table->dropUnique(['report_number']);
                }
            }
            $colsToDrop = array_filter([
                'report_number',
                'user_id',
                'zone',
                'validated_by',
                'validated_at',
                'rejection_reason',
            ], fn($c) => Schema::hasColumn('meter_readings', $c));

            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
