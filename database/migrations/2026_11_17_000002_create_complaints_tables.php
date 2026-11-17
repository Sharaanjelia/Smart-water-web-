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
        // 1. Complaint Categories
        Schema::create('complaint_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Complaints (Tiket Pengaduan Warga)
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique()->index(); // TK-PYK-2026-XXXX
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('category_id')->constrained('complaint_categories')->onDelete('restrict');
            $table->string('reporter_name');
            $table->string('contact_phone');
            $table->string('title');
            $table->text('description');
            $table->string('district')->default('Payakumbuh Barat');
            $table->text('address');
            $table->string('status')->default('BARU')->index(); // BARU, DIPROSES, MENUNGGU TINDAK LANJUT, SELESAI, DITOLAK
            $table->string('priority')->default('NORMAL'); // RENDAH, NORMAL, TINGGI, DARURAT
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Complaint Attachments (Foto Bukti Lapangan)
        Schema::create('complaint_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->default('image/jpeg');
            $table->timestamps();
        });

        // 4. Complaint Histories (Audit Trail & Tracking Status)
        Schema::create('complaint_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->string('actor_name')->default('Sistem Otomatis');
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_histories');
        Schema::dropIfExists('complaint_attachments');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('complaint_categories');
    }
};
