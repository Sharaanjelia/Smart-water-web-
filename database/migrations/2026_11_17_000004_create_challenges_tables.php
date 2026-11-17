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
        // 1. Challenges (Tantangan Konservasi Air Warga)
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('challenge_code')->unique(); // challenge-1, challenge-2, challenge-3
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('description');
            $table->string('level')->default('Mudah'); // Mudah, Menengah, Mahir
            $table->unsignedInteger('duration_days')->default(7);
            $table->unsignedInteger('xp_total')->default(250);
            $table->string('badge_code')->nullable();
            $table->string('badge_title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Challenge Missions (Daftar Misi Harian / Aksi)
        Schema::create('challenge_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->string('mission_code')->index(); // m-1-1, m-1-2
            $table->unsignedInteger('order_index')->default(1);
            $table->string('title');
            $table->unsignedInteger('xp')->default(50);
            $table->text('tip')->nullable();
            $table->timestamps();
        });

        // 3. Challenge Participants (Peserta Bergabung)
        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->string('status')->default('ACTIVE'); // ACTIVE, COMPLETED
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->unique(['user_id', 'challenge_id']);
            $table->timestamps();
        });

        // 4. Challenge Submissions (Penyelesaian Misi)
        Schema::create('challenge_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('challenge_participants')->onDelete('cascade');
            $table->foreignId('mission_id')->constrained('challenge_missions')->onDelete('cascade');
            $table->boolean('is_completed')->default(true);
            $table->text('proof_notes')->nullable();
            $table->string('proof_photo_path')->nullable();
            $table->timestamp('completed_at')->useCurrent();
            $table->unique(['participant_id', 'mission_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_submissions');
        Schema::dropIfExists('challenge_participants');
        Schema::dropIfExists('challenge_missions');
        Schema::dropIfExists('challenges');
    }
};
