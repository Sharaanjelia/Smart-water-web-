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
        // 1. Academy Categories
        Schema::create('academy_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Courses (Modul Kursus Akademi Air)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('academy_categories')->onDelete('cascade');
            $table->string('course_code')->unique(); // course-1, course-2, course-3
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('level')->default('Pemula');
            $table->unsignedInteger('estimated_minutes')->default(15);
            $table->unsignedInteger('xp_reward')->default(150);
            $table->string('badge_reward')->nullable(); // badge-sago-guardian, badge-water-scholar
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // 3. Lessons (Bab Materi Bacaan)
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('lesson_code')->index(); // lesson-1-1, lesson-1-2
            $table->unsignedInteger('order_index')->default(1);
            $table->string('title');
            $table->string('duration')->default('3 Menit');
            $table->longText('content')->nullable();
            $table->timestamps();
        });

        // 4. User Lesson Progress (Pelacakan Bacaan)
        Schema::create('user_lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->boolean('is_completed')->default(true);
            $table->timestamp('completed_at')->nullable();
            $table->unique(['user_id', 'lesson_id']);
            $table->timestamps();
        });

        // 5. User Quiz Results (Hasil Skor Kuis Akademi)
        Schema::create('user_quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->unsignedInteger('score')->default(0); // 0 - 100
            $table->unsignedInteger('xp_earned')->default(0);
            $table->boolean('is_passed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->unique(['user_id', 'course_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quiz_results');
        Schema::dropIfExists('user_lesson_progress');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('academy_categories');
    }
};
