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
        // 1. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'admin', 'staff', 'customer'
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Role User Pivot
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->unique(['user_id', 'role_id']);
            $table->timestamps();
        });

        // 3. Customer Profiles
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('customer_id')->index(); // ID Pelanggan Sambungan Air (contoh: 1234567890)
            $table->string('meter_number')->index(); // Nomor Seri Meteran (contoh: 01000)
            $table->string('tariff_category')->default('2A - Rumah Tangga Mandiri');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('district')->default('Payakumbuh Barat');
            $table->string('zone')->default('DIST-A Sektor Utara');
            $table->decimal('last_meter_value', 10, 2)->default(0);
            $table->decimal('current_meter_value', 10, 2)->default(0);
            $table->decimal('wallet_balance', 14, 2)->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });

        // 4. Staff / Operator Profiles
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('nip')->unique(); // NIP Pegawai Kedinasan
            $table->string('department')->default('Divisi Transmisi & SCADA');
            $table->string('position')->default('Operator SCADA & Petugas Lapangan');
            $table->string('phone_dinas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
