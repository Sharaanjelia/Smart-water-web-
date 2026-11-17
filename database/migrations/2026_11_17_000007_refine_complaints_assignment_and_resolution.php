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
        Schema::table('complaints', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('priority')->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            $table->timestamp('resolved_at')->nullable()->after('assigned_at');
            $table->text('resolution_notes')->nullable()->after('resolved_at');
            $table->text('rejection_reason')->nullable()->after('resolution_notes');
            
            $table->index(['status', 'district']);
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['status', 'district']);
            $table->dropIndex(['assigned_to']);
            $table->dropColumn(['assigned_to', 'assigned_at', 'resolved_at', 'resolution_notes', 'rejection_reason']);
        });
    }
};
