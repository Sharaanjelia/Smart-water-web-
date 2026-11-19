<?php

use App\Models\Role;
use App\Models\User;
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
        // 1. Buat Tabel Snapshot Audit Relasi Role (DDL di luar transaksi MySQL karena implicit commit)
        if (!Schema::hasTable('rbac_transition_logs')) {
            Schema::create('rbac_transition_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('previous_role_name');
                $table->string('new_role_name');
                $table->timestamps();
            });
        }

        DB::transaction(function () {
            // 2. Daftarkan Role Granular Baru ke Tabel roles
            $scadaRole = Role::firstOrCreate(
                ['name' => 'scada_operator'],
                [
                    'display_name' => 'Operator PDAM & SCADA',
                    'description'  => 'Operator teknis pengolahan air, telemetri reservoir, dan verifikator baca meter AI-OCR.',
                ]
            );

            $fieldRole = Role::firstOrCreate(
                ['name' => 'field_officer'],
                [
                    'display_name' => 'Petugas Lapangan (TRC)',
                    'description'  => 'Teknisi lapangan, survei sambungan baru, pencatatan fisik meteran, dan tindak lanjut aduan warga.',
                ]
            );

            $adminRole = Role::where('name', 'admin')->first();
            $customerRole = Role::where('name', 'customer')->first();
            $staffRole = Role::where('name', 'staff')->first();

            // 3. Transisi Atomik Ahmad Pratama -> scada_operator
            $ahmad = User::where('email', 'ahmad.pratama@tirtasago.id')->first();
            if ($ahmad) {
                if ($staffRole && $ahmad->roles()->where('role_id', $staffRole->id)->exists()) {
                    $ahmad->roles()->detach($staffRole->id);
                }
                if (!$ahmad->roles()->where('role_id', $scadaRole->id)->exists()) {
                    $ahmad->roles()->attach($scadaRole->id);
                }
                DB::table('rbac_transition_logs')->insert([
                    'user_id'            => $ahmad->id,
                    'previous_role_name' => 'staff',
                    'new_role_name'      => 'scada_operator',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            // 4. Transisi Atomik Dedi Kurniawan -> field_officer (TANPA data profil fiktif)
            $dedi = User::where('email', 'dedi.teknisi@tirtasago.id')->first();
            if ($dedi) {
                if ($staffRole && $dedi->roles()->where('role_id', $staffRole->id)->exists()) {
                    $dedi->roles()->detach($staffRole->id);
                }
                if (!$dedi->roles()->where('role_id', $fieldRole->id)->exists()) {
                    $dedi->roles()->attach($fieldRole->id);
                }
                DB::table('rbac_transition_logs')->insert([
                    'user_id'            => $dedi->id,
                    'previous_role_name' => 'staff',
                    'new_role_name'      => 'field_officer',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            // 5. Pastikan Hendra Pratama memegang role admin
            $hendra = User::where('email', 'admin@tirtasago.id')->first();
            if ($hendra && $adminRole && !$hendra->roles()->where('role_id', $adminRole->id)->exists()) {
                $hendra->roles()->attach($adminRole->id);
            }

            // 6. Pastikan Budi Santoso dan Siti Nurbaya memegang role customer
            $budi = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
            if ($budi && $customerRole && !$budi->roles()->where('role_id', $customerRole->id)->exists()) {
                $budi->roles()->attach($customerRole->id);
            }

            $siti = User::where('email', 'siti.nurbaya@payakumbuh.community')->first();
            if ($siti && $customerRole && !$siti->roles()->where('role_id', $customerRole->id)->exists()) {
                $siti->roles()->attach($customerRole->id);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $staffRole = Role::where('name', 'staff')->first();

        if (Schema::hasTable('rbac_transition_logs') && $staffRole) {
            DB::transaction(function () use ($staffRole) {
                $logs = DB::table('rbac_transition_logs')->get();

                foreach ($logs as $log) {
                    $user = User::find($log->user_id);
                    if ($user) {
                        $currentNewRole = Role::where('name', $log->new_role_name)->first();
                        if ($currentNewRole) {
                            $user->roles()->detach($currentNewRole->id);
                        }
                        if (!$user->roles()->where('role_id', $staffRole->id)->exists()) {
                            $user->roles()->attach($staffRole->id);
                        }
                    }
                }
            });

            Schema::dropIfExists('rbac_transition_logs');
        }
    }
};
