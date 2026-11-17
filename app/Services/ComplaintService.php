<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintCategory;
use App\Models\ComplaintHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComplaintService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Dapatkan daftar pengaduan dengan filter opsional dan eager loading lengkap.
     *
     * @return Collection<int, Complaint>
     */
    public function getComplaints(
        ?string $status = null,
        ?string $district = null,
        ?int $userId = null
    ): Collection {
        $query = Complaint::with(['category', 'attachments', 'histories', 'assignedStaff'])
            ->latest();

        if ($status) {
            $query->where('status', strtoupper(trim($status)));
        }

        if ($district) {
            $query->where('district', trim($district));
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Cari satu pengaduan berdasarkan ID numerik atau Nomor Tiket (TK-PYK-YYYY-XXXX).
     */
    public function findComplaint(string|int $idOrNumber): ?Complaint
    {
        return Complaint::with(['category', 'attachments', 'histories', 'assignedStaff'])
            ->where('ticket_number', (string)$idOrNumber)
            ->orWhere('id', (string)$idOrNumber)
            ->first();
    }

    /**
     * Buat tiket pengaduan baru lengkap dengan penomoran unik, attachment, dan riwayat status awal.
     */
    public function createComplaint(array $data, ?User $user = null): Complaint
    {
        return DB::transaction(function () use ($data, $user) {
            $categorySlug = trim((string)($data['category'] ?? 'pipa_bocor'));
            $category = ComplaintCategory::firstOrCreate(
                ['slug' => $categorySlug],
                ['name' => ucwords(str_replace('_', ' ', $categorySlug))]
            );

            $reporterName = $user ? $user->name : trim((string)($data['userName'] ?? 'Warga Payakumbuh'));
            $ticketNumber = $this->generateTicketNumber();

            $complaint = Complaint::create([
                'ticket_number' => $ticketNumber,
                'user_id'       => $user?->id,
                'category_id'   => $category->id,
                'reporter_name' => $reporterName,
                'contact_phone' => trim((string)($data['userPhone'] ?? '')),
                'title'         => trim((string)$data['title']),
                'description'   => trim((string)$data['description']),
                'district'      => trim((string)($data['district'] ?? 'Payakumbuh Barat')),
                'address'       => trim((string)$data['address']),
                'status'        => 'BARU',
                'priority'      => $data['priority'] ?? 'NORMAL',
            ]);

            // Simpan foto lampiran jika dikirim dalam Base64
            if (!empty($data['imageUrl'])) {
                $this->saveAttachment($complaint, $data['imageUrl']);
            }

            // Catat riwayat status pertama
            ComplaintHistory::create([
                'complaint_id'  => $complaint->id,
                'actor_name'    => 'Sistem Otomatis',
                'actor_user_id' => $user?->id,
                'status'        => 'BARU',
                'note'          => 'Laporan pengaduan berhasil tercatat dalam sistem pusat layanan pelanggan PDAM Tirta Sago.',
            ]);

            // Kirim notifikasi sambutan laporan kepada pelapor jika terdaftar
            if ($user) {
                $this->notificationService->send(
                    $user->id,
                    'SERVICE',
                    "Laporan Pengaduan #{$ticketNumber} Berhasil Dibuat",
                    "Laporan Anda mengenai \"{$complaint->title}\" telah masuk ke sistem dan menunggu verifikasi petugas.",
                    '/pusat-layanan'
                );
            }

            return $complaint->fresh(['category', 'attachments', 'histories', 'assignedStaff']);
        });
    }

    /**
     * Perbarui status pengaduan oleh petugas operasional.
     */
    public function updateStatus(
        string|int $idOrNumber,
        string $status,
        ?string $note = null,
        ?User $actor = null
    ): Complaint {
        return DB::transaction(function () use ($idOrNumber, $status, $note, $actor) {
            $complaint = $this->findComplaint($idOrNumber);
            if (!$complaint) {
                throw new \InvalidArgumentException('Tiket pengaduan tidak ditemukan.');
            }

            $status = strtoupper(trim($status));
            $actorName = $actor?->name ?? 'Operator SCADA';

            $complaint->update(['status' => $status]);

            ComplaintHistory::create([
                'complaint_id'  => $complaint->id,
                'actor_name'    => $actorName,
                'actor_user_id' => $actor?->id,
                'status'        => $status,
                'note'          => $note ?: "Status tiket diperbarui menjadi {$status}.",
            ]);

            if ($complaint->user_id) {
                $this->notificationService->send(
                    $complaint->user_id,
                    'SERVICE',
                    "Pembaruan Status Tiket #{$complaint->ticket_number}",
                    "Status laporan Anda telah diubah menjadi {$status} oleh {$actorName}.",
                    '/pusat-layanan'
                );
            }

            return $complaint->fresh(['category', 'attachments', 'histories', 'assignedStaff']);
        });
    }

    /**
     * Tugaskan petugas atau teknisi lapangan untuk menangani tiket pengaduan.
     */
    public function assignTechnician(
        string|int $idOrNumber,
        int $staffId,
        ?string $note = null,
        ?User $actor = null
    ): Complaint {
        return DB::transaction(function () use ($idOrNumber, $staffId, $note, $actor) {
            $complaint = $this->findComplaint($idOrNumber);
            if (!$complaint) {
                throw new \InvalidArgumentException('Tiket pengaduan tidak ditemukan.');
            }

            $technician = User::find($staffId) ?? $actor;
            if (!$technician) {
                throw new \InvalidArgumentException('Data teknisi yang ditugaskan tidak valid.');
            }

            $assignedNote = $note ?: "Penugasan kepada teknisi lapangan: {$technician->name}";

            $complaint->update([
                'assigned_to' => $technician->id,
                'assigned_at' => now(),
                'status'      => 'DIPROSES',
            ]);

            ComplaintHistory::create([
                'complaint_id'  => $complaint->id,
                'actor_name'    => $actor?->name ?? 'Pusat Kontrol PDAM',
                'actor_user_id' => $actor?->id,
                'status'        => 'DIPROSES',
                'note'          => $assignedNote,
            ]);

            if ($complaint->user_id) {
                $this->notificationService->send(
                    $complaint->user_id,
                    'SERVICE',
                    "Tiket {$complaint->ticket_number} Telah Ditugaskan",
                    "Laporan Anda telah diteruskan kepada teknisi {$technician->name} untuk tindakan lapangan.",
                    '/pusat-layanan'
                );
            }

            return $complaint->fresh(['category', 'attachments', 'histories', 'assignedStaff']);
        });
    }

    /**
     * Berikan tanggapan resmi dari tim lapangan pada tiket pengaduan.
     */
    public function addResponse(
        string|int $idOrNumber,
        string $note,
        ?User $actor = null
    ): Complaint {
        $complaint = $this->findComplaint($idOrNumber);
        if (!$complaint) {
            throw new \InvalidArgumentException('Tiket pengaduan tidak ditemukan.');
        }

        ComplaintHistory::create([
            'complaint_id'  => $complaint->id,
            'actor_name'    => $actor?->name ?? 'Petugas PDAM',
            'actor_user_id' => $actor?->id,
            'status'        => $complaint->status,
            'note'          => trim($note),
        ]);

        if ($complaint->user_id) {
            $this->notificationService->send(
                $complaint->user_id,
                'SERVICE',
                "Tanggapan Baru untuk Tiket {$complaint->ticket_number}",
                "Petugas PDAM: \"{$note}\"",
                '/pusat-layanan'
            );
        }

        return $complaint->fresh(['category', 'attachments', 'histories', 'assignedStaff']);
    }

    /**
     * Selesaikan tiket pengaduan secara tuntas.
     */
    public function resolveComplaint(
        string|int $idOrNumber,
        ?string $resolutionNotes = null,
        ?User $actor = null
    ): Complaint {
        return DB::transaction(function () use ($idOrNumber, $resolutionNotes, $actor) {
            $complaint = $this->findComplaint($idOrNumber);
            if (!$complaint) {
                throw new \InvalidArgumentException('Tiket pengaduan tidak ditemukan.');
            }

            $notes = $resolutionNotes ?: 'Perbaikan pipa dan aliran air telah normal.';

            $complaint->update([
                'status'           => 'SELESAI',
                'resolved_at'      => now(),
                'resolution_notes' => $notes,
            ]);

            ComplaintHistory::create([
                'complaint_id'  => $complaint->id,
                'actor_name'    => $actor?->name ?? 'Petugas PDAM',
                'actor_user_id' => $actor?->id,
                'status'        => 'SELESAI',
                'note'          => "Penyelesaian tuntas: {$notes}",
            ]);

            if ($complaint->user_id) {
                $this->notificationService->send(
                    $complaint->user_id,
                    'SERVICE',
                    "Tiket {$complaint->ticket_number} Telah Selesai Ditangani",
                    "Perumda Tirta Sago telah menyelesaikan perbaikan terkait laporan Anda. Terima kasih atas partisipasi Anda.",
                    '/pusat-layanan'
                );
            }

            return $complaint->fresh(['category', 'attachments', 'histories', 'assignedStaff']);
        });
    }

    /**
     * Tolak tiket pengaduan jika tidak sesuai atau data tidak valid.
     */
    public function rejectComplaint(
        string|int $idOrNumber,
        ?string $rejectionReason = null,
        ?User $actor = null
    ): Complaint {
        return DB::transaction(function () use ($idOrNumber, $rejectionReason, $actor) {
            $complaint = $this->findComplaint($idOrNumber);
            if (!$complaint) {
                throw new \InvalidArgumentException('Tiket pengaduan tidak ditemukan.');
            }

            $reason = $rejectionReason ?: 'Laporan ditolak setelah peninjauan fisik lapangan.';

            $complaint->update([
                'status'           => 'DITOLAK',
                'resolved_at'      => now(),
                'rejection_reason' => $reason,
            ]);

            ComplaintHistory::create([
                'complaint_id'  => $complaint->id,
                'actor_name'    => $actor?->name ?? 'Petugas PDAM',
                'actor_user_id' => $actor?->id,
                'status'        => 'DITOLAK',
                'note'          => "Laporan ditolak: {$reason}",
            ]);

            if ($complaint->user_id) {
                $this->notificationService->send(
                    $complaint->user_id,
                    'SERVICE',
                    "Tiket {$complaint->ticket_number} Ditolak",
                    "Laporan Anda tidak dapat ditindaklanjuti. Alasan: {$reason}",
                    '/pusat-layanan'
                );
            }

            return $complaint->fresh(['category', 'attachments', 'histories', 'assignedStaff']);
        });
    }

    /**
     * Generate nomor tiket berformat TK-PYK-YYYY-XXXX yang dijamin unik di database MySQL.
     */
    public function generateTicketNumber(): string
    {
        $year = date('Y');
        do {
            $randomCode = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $ticketNumber = "TK-PYK-{$year}-{$randomCode}";
        } while (Complaint::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    /**
     * Simpan file gambar Base64 secara aman ke storage publik dan buat record ComplaintAttachment.
     */
    public function saveAttachment(Complaint $complaint, string $base64Data): ?ComplaintAttachment
    {
        try {
            $mimeType = 'image/jpeg';
            $ext = 'jpg';

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($type[1]);
                $mimeType = "image/{$ext}";
                if ($ext === 'jpeg') $ext = 'jpg';
            }

            $decoded = base64_decode($base64Data, true);
            if ($decoded === false) {
                return null;
            }

            $filename = 'complaint_' . $complaint->id . '_' . Str::random(10) . '.' . $ext;
            $path = 'complaints/' . $filename;

            Storage::disk('public')->put($path, $decoded);

            return ComplaintAttachment::create([
                'complaint_id' => $complaint->id,
                'file_path'    => 'storage/' . $path,
                'file_name'    => $filename,
                'file_size'    => strlen($decoded),
                'mime_type'    => $mimeType,
            ]);
        } catch (\Throwable $e) {
            // Kegagalan simpan attachment tidak boleh membatalkan pencatatan pengaduan utama
            return null;
        }
    }
}
