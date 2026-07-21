<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Services\FonnteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWhatsAppPaymentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected FonnteService $fonnteService;

    /**
     * Create the event listener.
     */
    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentConfirmed $event): void
    {
        $booking = $event->booking->loadMissing(['kos.pemilik', 'penyewa']);

        $kos = $booking->kos;
        $penyewa = $booking->penyewa;
        $pemilik = $kos ? $kos->pemilik : null;

        Log::info('SendWhatsAppPaymentNotification: Memproses queue notifikasi pembayaran', [
            'booking_id' => $booking->id,
            'penyewa_id' => $penyewa ? $penyewa->id : null,
            'pemilik_id' => $pemilik ? $pemilik->id : null,
        ]);

        // 1. Notifikasi WhatsApp ke Penyewa
        if ($penyewa && !empty($penyewa->no_hp)) {
            $pesanPenyewa = "Halo {$penyewa->nama},\n\n"
                . "Pembayaran booking kos Anda telah *BERHASIL* dikonfirmasi!\n\n"
                . "*Detail Booking:*\n"
                . "- Nama Kos: " . ($kos ? $kos->nama_kos : '-') . "\n"
                . "- Tanggal Masuk: " . ($booking->tanggal_masuk ? $booking->tanggal_masuk->format('d-m-Y') : '-') . "\n"
                . "- Durasi: {$booking->durasi_bulan} Bulan\n"
                . "- Total Bayar: Rp " . number_format((float)$booking->total_harga, 0, ',', '.') . "\n\n"
                . "Terima kasih telah menggunakan layanan Kostin!";

            $this->fonnteService->sendMessage($penyewa->no_hp, $pesanPenyewa);
        }

        // 2. Notifikasi WhatsApp ke Pemilik Kos
        if ($pemilik && !empty($pemilik->no_hp)) {
            $pesanPemilik = "Halo Bapak/Ibu {$pemilik->nama},\n\n"
                . "Ada pembayaran booking baru yang *BERHASIL* untuk kos Anda!\n\n"
                . "*Detail Booking:*\n"
                . "- Nama Kos: " . ($kos ? $kos->nama_kos : '-') . "\n"
                . "- Nama Penyewa: " . ($penyewa ? $penyewa->nama : '-') . "\n"
                . "- Nomor HP Penyewa: " . ($penyewa ? $penyewa->no_hp : '-') . "\n"
                . "- Tanggal Masuk: " . ($booking->tanggal_masuk ? $booking->tanggal_masuk->format('d-m-Y') : '-') . "\n"
                . "- Durasi: {$booking->durasi_bulan} Bulan\n\n"
                . "Silakan cek dashboard Kostin Anda untuk detail lebih lanjut.";

            $this->fonnteService->sendMessage($pemilik->no_hp, $pesanPemilik);
        }
    }
}