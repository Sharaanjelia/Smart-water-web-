<?php
/**
 * Endpoint API Operasional Command Center Admin SCADA
 * Terhubung ke Database MySQL (disposisi tiket, validasi meteran AI-OCR, pengumuman, audit trail).
 */

use App\Models\Complaint;
use App\Models\ComplaintHistory;
use App\Models\MeterReading;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

header('Content-Type: application/json; charset=utf-8');

$SWC_PAGE_MODULE = 'php-admin';
require_once dirname(__DIR__, 2) . '/app/LegacyConfig.php';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? (function_exists('request') ? request()->method() : 'GET');
if ($requestMethod !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Metode tidak diizinkan.']);
    return;
}

$raw = file_get_contents('php://input');
if ((!$raw || $raw === '') && function_exists('request')) {
    $raw = request()->getContent();
}
$payload = json_decode($raw ?: '[]', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$action = trim((string) ($payload['action'] ?? ''));
$state  = &$_SESSION['swc_admin_state'];
$nowWib = date('d M Y H:i') . ' WIB';
$defaultActor = Auth::user()?->name ?? $DEFAULT_ADMIN_USER['name'];

$pushAudit = function (string $actor, string $actCode, string $target, string $details) use (&$state, $nowWib) {
    if (!isset($state['auditLogs'])) $state['auditLogs'] = [];
    array_unshift($state['auditLogs'], [
        'id'        => 'log-' . substr(md5(uniqid((string) mt_rand(), true)), 0, 6),
        'timestamp' => $nowWib,
        'actor'     => $actor ?: 'Ir. Hendra Pratama, S.T.',
        'action'    => $actCode,
        'target'    => $target,
        'details'   => $details,
    ]);
    if (count($state['auditLogs']) > 50) {
        $state['auditLogs'] = array_slice($state['auditLogs'], 0, 50);
    }
};

switch ($action) {
    case 'update_ticket':
        $ticketId = trim((string) ($payload['ticketId'] ?? ''));
        $status   = trim((string) ($payload['status'] ?? 'DIPROSES'));
        $actor    = trim((string) ($payload['actor'] ?? $defaultActor));
        $note     = trim((string) ($payload['note'] ?? ''));

        $allowed = ['BARU', 'DIPROSES', 'MENUNGGU TINDAK LANJUT', 'SELESAI'];
        if (!in_array($status, $allowed, true)) {
            echo json_encode(['ok' => false, 'message' => 'Status tiket tidak valid.']);
            return;
        }

        // 1. Simpan ke Database MySQL
        $dbComplaint = Complaint::where('ticket_number', $ticketId)->first();
        if ($dbComplaint) {
            $dbComplaint->status = $status;
            $dbComplaint->save();

            ComplaintHistory::create([
                'complaint_id'  => $dbComplaint->id,
                'actor_name'    => $actor,
                'actor_user_id' => Auth::id(),
                'status'        => $status,
                'note'          => $note ?: "Status tiket diperbarui menjadi {$status}.",
            ]);
        }

        // 2. Sinkronkan dengan state sesi
        $updatedTicket = null;
        if (isset($state['complaints'])) {
            foreach ($state['complaints'] as &$t) {
                if ($t['id'] === $ticketId) {
                    $t['status']    = $status;
                    $t['updatedAt'] = $nowWib;
                    $finalNote      = $note !== '' ? $note : "Status tiket diperbarui menjadi {$status}";
                    $t['updates'][] = [
                        'timestamp' => $nowWib,
                        'status'    => $status,
                        'note'      => $finalNote,
                        'actor'     => $actor ?: $defaultActor,
                    ];
                    $updatedTicket = $t;
                    $pushAudit($actor ?: $defaultActor, 'DISPATCH_TIKET', $ticketId, "Status diubah ke {$status}. {$finalNote}");
                    break;
                }
            }
            unset($t);
        }

        if (!$updatedTicket && $dbComplaint) {
            $updatedTicket = \App\Http\Controllers\ComplaintController::formatTicket($dbComplaint);
        }

        echo json_encode([
            'ok'            => true,
            'message'       => "Tiket {$ticketId} berhasil diperbarui ke status \"{$status}\"!",
            'updatedTicket' => $updatedTicket,
            'state'         => $state,
        ]);
        return;

    case 'delete_ticket':
        $ticketId = trim((string) ($payload['ticketId'] ?? ''));

        // Hapus dari database MySQL
        $dbComplaint = Complaint::where('ticket_number', $ticketId)->first();
        if ($dbComplaint) {
            $dbComplaint->delete();
        }

        if (isset($state['complaints'])) {
            $state['complaints'] = array_values(array_filter(
                $state['complaints'],
                fn($t) => $t['id'] !== $ticketId
            ));
        }

        $pushAudit($defaultActor, 'HAPUS_TIKET', $ticketId, "Tiket pengaduan {$ticketId} dihapus.");
        echo json_encode([
            'ok'      => true,
            'message' => "Tiket {$ticketId} berhasil dihapus dari database.",
            'state'   => $state,
        ]);
        return;

    case 'approve_meter':
        $meterId = trim((string) ($payload['meterId'] ?? ''));

        // Update di database MySQL
        $reading = MeterReading::where('report_number', $meterId)->orWhere('id', $meterId)->first();
        if ($reading) {
            $reading->update([
                'status'       => 'Terverifikasi AI',
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);
        }

        $found = null;
        if (isset($state['meterVerifications'])) {
            foreach ($state['meterVerifications'] as &$m) {
                if ($m['id'] === $meterId) {
                    $m['status']     = 'terverifikasi';
                    $m['verifiedBy'] = $defaultActor;
                    $m['verifiedAt'] = $nowWib;
                    $m['notes']      = 'Angka stand terverifikasi akurat dan tagihan rekening otomatis disahkan.';
                    $found           = $m;
                    $pushAudit(
                        $defaultActor,
                        'VERIFIKASI_METER',
                        "{$m['meterNumber']} ({$m['customerName']})",
                        "Stand meter {$m['recordedStand']} m³ disetujui operator."
                    );
                    break;
                }
            }
            unset($m);
        }

        echo json_encode([
            'ok'      => true,
            'message' => "Stand meteran ({$meterId}) BERHASIL diverifikasi ke database!",
            'state'   => $state,
        ]);
        return;

    case 'add_announcement':
        $title       = trim((string) ($payload['title'] ?? ''));
        $category    = trim((string) ($payload['category'] ?? 'pemeliharaan'));
        $estimated   = trim((string) ($payload['estimatedResolution'] ?? 'Hari ini, 18:00 WIB'));
        $description = trim((string) ($payload['description'] ?? ''));
        $advice      = trim((string) ($payload['actionAdvice'] ?? ''));
        $source      = trim((string) ($payload['source'] ?? 'Divisi Distribusi PDAM Tirta Sago'));
        $districts   = $payload['districtsAffected'] ?? ['Payakumbuh Barat'];

        if ($title === '' || $description === '') {
            echo json_encode(['ok' => false, 'message' => 'Judul dan deskripsi pengumuman wajib diisi.']);
            return;
        }

        // Buat notifikasi di database untuk seluruh user
        $users = User::all();
        foreach ($users as $u) {
            Notification::create([
                'user_id' => $u->id,
                'type'    => 'SERVICE',
                'title'   => $title,
                'desc'    => $description . ' ' . $advice,
                'link'    => '/water-condition',
            ]);
        }

        $newId = 'ANN-2026-' . str_pad((string) mt_rand(10, 99), 2, '0', STR_PAD_LEFT) . '-' . str_pad((string) mt_rand(10, 99), 2, '0', STR_PAD_LEFT);
        $newAnn = [
            'id'                  => $newId,
            'title'               => $title,
            'category'            => $category,
            'districtsAffected'   => array_values(array_map('strval', is_array($districts) ? $districts : [$districts])),
            'status'              => 'aktif',
            'publishedAt'         => date('Y-m-d H:i') . ' WIB',
            'estimatedResolution' => $estimated !== '' ? $estimated : 'Sesuai perkembangan teknis',
            'description'         => $description,
            'actionAdvice'        => $advice !== '' ? $advice : 'Pelanggan diimbau menampung air cadangan secukupnya.',
            'source'              => $source !== '' ? $source : 'PDAM Tirta Sago Kota Payakumbuh',
        ];
        if (!isset($state['announcements'])) $state['announcements'] = [];
        array_unshift($state['announcements'], $newAnn);
        $pushAudit($defaultActor, 'PUBLISH_PENGUMUMAN', $newId, "Menerbitkan pengumuman baru: {$title}");

        echo json_encode([
            'ok'      => true,
            'message' => 'Pengumuman resmi berhasil diterbitkan ke seluruh pelanggan!',
            'state'   => $state,
        ]);
        return;

    case 'update_valve':
        $resId    = trim((string) ($payload['resId'] ?? ''));
        $valvePct = max(0, min(100, (int) ($payload['valveOpenPct'] ?? 80)));

        $found = null;
        if (isset($state['reservoirs'])) {
            foreach ($state['reservoirs'] as &$res) {
                if ($res['id'] === $resId) {
                    $res['valveOpenPct'] = $valvePct;
                    $res['lastUpdated']  = 'Baru saja';
                    $found               = $res;
                    $pushAudit($defaultActor, 'SCADA_VALVE', $res['name'], "Bukaan katup motorik diatur ke {$valvePct}%.");
                    break;
                }
            }
            unset($res);
        }

        echo json_encode([
            'ok'      => true,
            'message' => "Bukaan katup berhasil disesuaikan ke {$valvePct}%.",
            'state'   => $state,
        ]);
        return;

    case 'reset_demo':
        $_SESSION['swc_admin_state'] = getInitialAdminSeedState();
        echo json_encode([
            'ok'      => true,
            'message' => 'Data operasional SCADA dikembalikan ke kondisi awal.',
            'state'   => $_SESSION['swc_admin_state'],
        ]);
        return;

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Aksi tidak dikenali.']);
        return;
}
