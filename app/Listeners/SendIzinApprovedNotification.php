<?php

namespace App\Listeners;

use App\Events\IzinApproved;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendIzinApprovedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(IzinApproved $event): void
    {
        try {
            $izinCuti = $event->izinCuti;
            $guru = $izinCuti->guru;

            if (!$guru || !$guru->user) {
                return;
            }

            // Send notification to guru
            $statusText = $izinCuti->status === 'disetujui' ? 'disetujui' : 'ditolak';
            $title = 'Izin/Cuti ' . ucfirst($statusText);

            $message = "Permohonan {$izinCuti->jenis} Anda telah {$statusText}";

            if ($izinCuti->status === 'ditolak' && $izinCuti->alasan_penolakan) {
                $message .= ". Alasan: {$izinCuti->alasan_penolakan}";
            }

            $this->notificationService->send(
                $guru->user,
                $title,
                $message,
                $izinCuti->status === 'disetujui' ? 'success' : 'danger',
                '/guru/izin/' . $izinCuti->id
            );

            // If approved and guru pengganti assigned, notify them
            if ($izinCuti->status === 'disetujui' && $izinCuti->guru_pengganti_id) {
                $guruPengganti = $izinCuti->guruPengganti;

                if ($guruPengganti && $guruPengganti->user) {
                    $this->notificationService->send(
                        $guruPengganti->user,
                        'Tugas Pengganti',
                        "Anda ditugaskan menggantikan {$guru->nama} pada " .
                        $izinCuti->tanggal_mulai->format('d/m/Y') . ' - ' .
                        $izinCuti->tanggal_selesai->format('d/m/Y'),
                        'info',
                        '/guru/jadwal'
                    );
                }
            }

            Log::info("SendIzinApprovedNotification: Notification sent for izin ID {$izinCuti->id}");

        } catch (\Exception $e) {
            Log::error('SendIzinApprovedNotification failed: ' . $e->getMessage());
        }
    }
}
