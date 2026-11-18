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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_number', 60)->unique();
                $table->foreignId('bill_id')->constrained('bills')->restrictOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('payment_method', 50)->default('manual_transfer');
                $table->string('bank_name', 100)->nullable();
                $table->string('sender_name', 150)->nullable();
                $table->string('sender_account_number', 60)->nullable();
                $table->string('proof_photo_path', 255);
                $table->string('reference_number', 100)->nullable();
                $table->enum('status', ['PENDING', 'VERIFIED', 'REJECTED'])->default('PENDING');
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('verified_at')->nullable();
                $table->string('rejection_reason', 255)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['bill_id', 'status']);
                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('payment_histories')) {
            Schema::create('payment_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
                $table->string('previous_status', 50)->nullable();
                $table->string('new_status', 50);
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_role', 50)->default('customer');
                $table->text('action_note')->nullable();
                $table->timestamps();

                $table->index(['payment_id', 'new_status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
        Schema::dropIfExists('payments');
    }
};
