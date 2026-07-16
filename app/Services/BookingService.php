<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Kos;
use App\Models\User;
use App\Models\PembagianDana;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;

class BookingService
{
    /**
     * Membuat Booking baru & mengintegrasikannya dengan Midtrans Snap.
     *
     * @param int $userId
     * @param array $data
     * @return Booking
     * @throws \Exception
     */
    public function createBooking(int $userId, array $data): Booking
    {
        return DB::transaction(function () use ($userId, $data) {
            $kos = Kos::findOrFail($data['kos_id']);
            $user = User::findOrFail($userId);

            // Validasi apakah kos aktif
            if ($kos->status !== 'aktif') {
                throw new \Exception("Kos ini sedang tidak aktif atau tidak menerima pesanan saat ini.");
            }

            // Validasi ketersediaan kamar
            if ($kos->kamar_terisi >= $kos->jumlah_kamar) {
                throw new \Exception("Kamar kos sudah penuh!");
            }

            // Hitung tanggal_keluar & total_harga
            $tanggalMasuk = Carbon::parse($data['tanggal_masuk']);
            $durasiBulan = (int) $data['durasi_bulan'];
            $tanggalKeluar = $tanggalMasuk->copy()->addMonths($durasiBulan)->format('Y-m-d');
            $totalHarga = $durasiBulan * (float) $kos->harga_per_bulan;

            // Simpan data booking ke database dengan status default 'menunggu_pembayaran'
            $booking = Booking::create([
                'kos_id'            => $kos->id,
                'penyewa_id'        => $user->id,
                'nomor_kamar'       => $data['nomor_kamar'] ?? null,
                'tanggal_masuk'     => $tanggalMasuk->format('Y-m-d'),
                'durasi_bulan'      => $durasiBulan,
                'tanggal_keluar'    => $tanggalKeluar,
                'harga_per_bulan'   => $kos->harga_per_bulan,
                'total_harga'       => $totalHarga,
                'status'            => 'menunggu_pembayaran',
                'catatan_penyewa'   => $data['catatan_penyewa'] ?? null,
            ]);

            // Konfigurasi Midtrans
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = config('midtrans.is_sanitized');
            Config::$is3ds = config('midtrans.is_3ds');

            $midtransOrderId = 'BOOK-' . $booking->id . '-' . time();

            // Payload Transaksi Midtrans
            $params = [
                'transaction_details' => [
                    'order_id'     => $midtransOrderId,
                    'gross_amount' => (int) $totalHarga,
                ],
                'customer_details' => [
                    'first_name' => $user->nama,
                    'email'      => $user->email,
                    'phone'      => $user->no_hp,
                ],
                'item_details' => [
                    [
                        'id'       => $kos->id,
                        'price'    => (int) $kos->harga_per_bulan,
                        'quantity' => $durasiBulan,
                        'name'     => 'Sewa Kos: ' . substr($kos->nama_kos, 0, 40),
                    ]
                ]
            ];

            try {
                // Mendapatkan snap_token
                $snapToken = Snap::getSnapToken($params);
            } catch (\Exception $e) {
                Log::error('Gagal mendapatkan Snap Token Midtrans: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'payload'    => $params
                ]);
                throw new \Exception('Terjadi kesalahan pada sistem pembayaran Midtrans: ' . $e->getMessage());
            }

            // Simpan snap_token dan midtrans_order_id ke booking
            $booking->update([
                'snap_token'        => $snapToken,
                'midtrans_order_id' => $midtransOrderId,
            ]);

            // Catat Log detail transaksi
            Log::info('Booking berhasil dibuat & Snap Token terbit', [
                'booking_id'        => $booking->id,
                'midtrans_order_id' => $midtransOrderId,
                'snap_token'        => $snapToken,
                'penyewa_id'        => $user->id,
                'total_harga'       => $totalHarga,
            ]);

            return $booking->load('kos');
        });
    }

    /**
     * Mendapatkan daftar booking berdasarkan user ID dan role.
     *
     * @param int $userId
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBookings(int $userId, string $role)
    {
        if ($role === 'pemilik') {
            return Booking::whereHas('kos', function ($query) use ($userId) {
                $query->where('pemilik_id', $userId);
            })->with(['kos', 'penyewa'])->orderBy('created_at', 'desc')->get();
        }

        // Pencari / Default
        return Booking::where('penyewa_id', $userId)->with(['kos'])->orderBy('created_at', 'desc')->get();
    }

    /**
     * Membatalkan booking oleh pencari.
     *
     * @param int $bookingId
     * @param int $userId
     * @return Booking
     * @throws \Exception
     */
    public function cancelBooking(int $bookingId, int $userId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if ((int)$booking->penyewa_id !== $userId) {
            throw new \Exception("Anda tidak memiliki akses untuk membatalkan booking ini.");
        }

        if ($booking->status !== 'menunggu_pembayaran') {
            throw new \Exception("Booking tidak dapat dibatalkan karena status saat ini adalah: " . $booking->status);
        }

        $booking->update(['status' => 'dibatalkan']);

        Log::info('Booking berhasil dibatalkan oleh pencari', [
            'booking_id' => $bookingId,
            'user_id'    => $userId,
        ]);

        return $booking;
    }

    /**
     * Menyelesaikan sewa booking oleh pemilik.
     *
     * @param int $bookingId
     * @param int $ownerId
     * @return Booking
     * @throws \Exception
     */
    public function completeBooking(int $bookingId, int $ownerId): Booking
    {
        $booking = Booking::with('kos')->findOrFail($bookingId);

        if ((int)$booking->kos->pemilik_id !== $ownerId) {
            throw new \Exception("Anda tidak berhak menyelesaikan booking ini.");
        }

        if (in_array($booking->status, ['dibatalkan', 'ditolak', 'selesai'])) {
            throw new \Exception("Booking dengan status '{$booking->status}' tidak dapat diselesaikan.");
        }

        $booking->update(['status' => 'selesai']);

        Log::info('Booking berhasil ditandai selesai oleh pemilik kos', [
            'booking_id' => $bookingId,
            'owner_id'   => $ownerId,
        ]);

        return $booking;
    }

    /**
     * Memproses notifikasi webhook dari Midtrans.
     *
     * @param array $notif
     * @return array
     * @throws \Exception
     */
    public function processWebhook(array $notif): array
    {
        $orderId = $notif['order_id'] ?? '';
        $statusCode = $notif['status_code'] ?? '';
        $grossAmount = $notif['gross_amount'] ?? '';
        $signatureKey = $notif['signature_key'] ?? '';
        $transactionStatus = $notif['transaction_status'] ?? '';
        $paymentType = $notif['payment_type'] ?? '';
        $fraudStatus = $notif['fraud_status'] ?? '';

        // 1. Verifikasi Signature SHA512
        $serverKey = config('midtrans.server_key');
        $computedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($computedSignature !== $signatureKey) {
            Log::error('Verifikasi Signature Webhook Midtrans GAGAL', [
                'order_id' => $orderId,
                'received' => $signatureKey,
                'computed' => $computedSignature
            ]);
            throw new \Exception("Invalid Midtrans Signature Key", 403);
        }

        // 2. Cari Booking berdasarkan midtrans_order_id
        $booking = Booking::where('midtrans_order_id', $orderId)->first();
        if (!$booking) {
            Log::info("Webhook Midtrans diabaikan (ignore): Booking tidak ditemukan untuk order_id: {$orderId}");
            return [
                'status' => 'ignored',
                'message' => "Booking not found for order_id: {$orderId}"
            ];
        }

        // 3. Logika status update jika settlement atau capture (fraud accept)
        $isSettlement = ($transactionStatus === 'settlement');
        $isCaptureAccept = ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        if ($isSettlement || $isCaptureAccept) {
            // Pastikan booking belum aktif atau selesai agar tidak double processing
            if ($booking->status !== 'aktif' && $booking->status !== 'selesai') {
                
                // MAPPING: Terjemahkan payment_type Midtrans ke nilai ENUM metode_pembayaran DB Anda
                $metodePembayaran = 'transfer_bank'; // default
                if ($paymentType === 'bank_transfer' || $paymentType === 'echannel') {
                    $metodePembayaran = 'transfer_bank';
                } elseif (in_array($paymentType, ['gopay', 'shopeepay'])) {
                    $metodePembayaran = 'ewallet';
                } elseif ($paymentType === 'qris') {
                    $metodePembayaran = 'qris';
                }

                DB::transaction(function () use ($booking, $paymentType, $metodePembayaran, $transactionStatus) {
                    // Update Status Booking ke aktif
                    $booking->update([
                        'status'            => 'aktif',
                        'payment_type'      => $paymentType,
                        'metode_pembayaran' => $metodePembayaran,
                        'tanggal_bayar'     => now(),
                        'midtrans_status'   => $transactionStatus,
                    ]);

                    // Increment kamar_terisi pada kos terkait (+1)
                    $kos = $booking->kos;
                    if ($kos) {
                        $kos->increment('kamar_terisi');
                        Log::info("Kamar terisi untuk Kos ID {$kos->id} bertambah menjadi {$kos->kamar_terisi}");
                    }

                    // Catat bagi hasil pemilik kos
                    $this->recordDanaDisbursement($booking->id);
                });

                Log::info("Webhook Midtrans SUKSES: Booking ID {$booking->id} diperbarui menjadi aktif.", [
                    'order_id' => $orderId,
                    'transaction_status' => $transactionStatus
                ]);

                return [
                    'status' => 'success',
                    'message' => 'Booking updated to active successfully'
                ];
            } else {
                Log::info("Webhook Midtrans diabaikan (ignore): Booking ID {$booking->id} sudah memiliki status '{$booking->status}'", [
                    'order_id' => $orderId
                ]);
                return [
                    'status' => 'ignored',
                    'message' => "Booking is already {$booking->status}"
                ];
            }
        }

        // Jika status transaksi lain (misalnya: pending, deny, cancel, expire)
        $booking->update([
            'midtrans_status' => $transactionStatus
        ]);

        Log::info("Webhook Midtrans diproses: Status Midtrans Booking ID {$booking->id} diperbarui menjadi {$transactionStatus}", [
            'order_id' => $orderId
        ]);

        return [
            'status' => 'processed',
            'message' => "Transaction status {$transactionStatus} processed"
        ];
    }

    /**
     * Mencatat pembagian dana bagi hasil pemilik kos.
     *
     * @param int $bookingId
     * @return PembagianDana
     */
    public function recordDanaDisbursement(int $bookingId): PembagianDana
    {
        $booking = Booking::with('kos')->findOrFail($bookingId);
        
        $totalTransaksi = (float) $booking->total_harga;
        $persenPlatform = 3.00;
        $biayaPlatform = $totalTransaksi * 0.03;
        $biayaGateway = 0.00;
        $jatahPemilik = $totalTransaksi - $biayaPlatform;

        $pembagian = PembagianDana::create([
            'booking_id'          => $booking->id,
            'pemilik_id'          => $booking->kos->pemilik_id,
            'total_transaksi'     => $totalTransaksi,
            'persen_platform'     => $persenPlatform,
            'biaya_platform'      => $biayaPlatform,
            'biaya_gateway'       => $biayaGateway,
            'jatah_pemilik'       => $jatahPemilik,
            'status_disbursement' => 'pending',
            'catatan'             => 'Pencatatan otomatis pembagian dana via webhook Midtrans.',
        ]);

        Log::info('Bagi hasil (disbursement) berhasil dicatat', [
            'booking_id'      => $booking->id,
            'pemilik_id'      => $booking->kos->pemilik_id,
            'total_transaksi' => $totalTransaksi,
            'biaya_platform'  => $biayaPlatform,
            'jatah_pemilik'   => $jatahPemilik,
        ]);

        return $pembagian;
    }
}