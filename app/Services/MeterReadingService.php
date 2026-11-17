<?php

namespace App\Services;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MeterReadingService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Dapatkan daftar laporan pembacaan meter dengan filter user atau status.
     *
     * @return Collection<int, MeterReading>
     */
    public function getReadings(?int $userId = null, ?string $status = null): Collection
    {
        $query = MeterReading::with(['user.customerProfile', 'validator'])->latest();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Cari laporan meter berdasarkan ID numerik atau Nomor Laporan (REP-04-XXXX).
     */
    public function findReading(string|int $idOrNumber): ?MeterReading
    {
        return MeterReading::with(['user.customerProfile', 'validator'])
            ->where('report_number', (string)$idOrNumber)
            ->orWhere('id', (string)$idOrNumber)
            ->first();
    }

    /**
     * Simpan laporan pembacaan meteran baru, proses hasil AI-OCR, dan hitung konsumsi air.
     */
    public function createReading(array $data, ?User $user = null): MeterReading
    {
        return DB::transaction(function () use ($data, $user) {
            $meterNumber  = trim((string)($data['meter'] ?? $data['meter_number'] ?? '01000'));
            $ocrReading   = (float)($data['ocrReading'] ?? $data['reading'] ?? 0);
            $lastReading  = (float)($data['lastReading'] ?? $data['previous_reading'] ?? 0);
            $customerName = trim((string)($data['name'] ?? $data['customer_name'] ?? ''));
            $zone         = trim((string)($data['zone'] ?? 'DIST-A Sektor Utara'));
            $notes        = trim((string)($data['notes'] ?? 'Laporan via Smart Water AI-OCR'));

            if (!$customerName && $user) {
                $customerName = $user->name;
            }

            // Jika stand lalu tidak disertakan, cari riwayat pembacaan terakhir dari nomor meter
            if ($lastReading <= 0) {
                $previousRecord = MeterReading::where('meter_number', $meterNumber)->latest()->first();
                $lastReading = $previousRecord ? (float)$previousRecord->reading : max(0, round($ocrReading - 19.0, 2));
            }

            // Hitung pemakaian kubikasi dan estimasi biaya berdasarkan tarif resmi
            $consumption = max(0, round($ocrReading - $lastReading, 2));
            $estimatedCost = round(($consumption * 7800) + 2600);

            $reportCode = $this->generateReportNumber();

            // Simpan foto fisik meteran jika ada (Base64)
            $imagePath = null;
            if (!empty($data['image'])) {
                $imagePath = $this->saveMeterImage($reportCode, $data['image']);
            }

            return MeterReading::create([
                'report_number'    => $reportCode,
                'user_id'          => $user?->id,
                'customer_name'    => $customerName ?: 'Pelanggan Mandiri',
                'meter_number'     => $meterNumber,
                'reading'          => $ocrReading,
                'previous_reading' => $lastReading,
                'consumption'      => $consumption,
                'estimated_cost'   => $estimatedCost,
                'confidence'       => $data['confidence'] ?? 98.4,
                'image_path'       => $imagePath,
                'zone'             => $zone,
                'notes'            => $notes,
                'status'           => 'Menunggu Validasi',
            ]);
        });
    }

    /**
     * Petugas memvalidasi (menyetujui atau menolak) laporan stand meter.
     */
    public function validateReading(
        string|int $idOrNumber,
        string $action,
        ?string $reason = null,
        ?User $validator = null
    ): MeterReading {
        return DB::transaction(function () use ($idOrNumber, $action, $reason, $validator) {
            $reading = $this->findReading($idOrNumber);
            if (!$reading) {
                throw new \InvalidArgumentException('Laporan meter tidak ditemukan.');
            }

            $actionUpper = strtoupper(trim($action));

            if ($actionUpper === 'APPROVE' || $actionUpper === 'TERVERIFIKASI AI') {
                $reading->update([
                    'status'           => 'Terverifikasi AI',
                    'validated_by'     => $validator?->id,
                    'validated_at'     => now(),
                    'rejection_reason' => null,
                ]);

                // Sinkronkan nilai meter terkini ke profil pelanggan di database
                if ($reading->user && $reading->user->customerProfile) {
                    $reading->user->customerProfile->update([
                        'current_meter_value' => $reading->reading,
                        'last_meter_value'    => $reading->previous_reading,
                    ]);
                }

                if ($reading->user_id) {
                    $this->notificationService->send(
                        $reading->user_id,
                        'METER',
                        'Laporan Meteran Berhasil Diverifikasi',
                        "Angka stand {$reading->reading} m³ telah disetujui petugas PDAM. Lembar tagihan resmi telah diperbarui.",
                        '/bill'
                    );
                }

                return $reading->fresh(['user.customerProfile', 'validator']);
            }

            // Aksi penolakan
            $rejectionReason = $reason ?: 'Foto buram atau angka tidak sesuai dial fisik meteran.';
            $reading->update([
                'status'           => 'Ditolak',
                'validated_by'     => $validator?->id,
                'validated_at'     => now(),
                'rejection_reason' => $rejectionReason,
            ]);

            if ($reading->user_id) {
                $this->notificationService->send(
                    $reading->user_id,
                    'METER',
                    'Laporan Meteran Memerlukan Pengecekan Ulang',
                    "Laporan stand {$reading->reading} m³ ditolak petugas: {$rejectionReason}. Mohon foto kembali dengan pencahayaan jelas.",
                    '/report-meter'
                );
            }

            return $reading->fresh(['user.customerProfile', 'validator']);
        });
    }

    /**
     * Generate kode nomor laporan berformat REP-04-XXXX yang dijamin unik.
     */
    public function generateReportNumber(): string
    {
        do {
            $code = 'REP-04-' . str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (MeterReading::where('report_number', $code)->exists());

        return $code;
    }

    /**
     * Simpan foto dial fisik meteran dari string Base64.
     */
    protected function saveMeterImage(string $reportCode, string $base64Data): ?string
    {
        try {
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($type[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
            }

            $decoded = base64_decode($base64Data, true);
            if ($decoded === false) {
                return null;
            }

            $filename = 'meter_' . str_replace('-', '_', strtolower($reportCode)) . '_' . Str::random(6) . '.' . $ext;
            $path = 'meters/' . $filename;

            Storage::disk('public')->put($path, $decoded);

            return 'storage/' . $path;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
