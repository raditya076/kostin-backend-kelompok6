<?php

namespace App\Services;

use App\Models\User;
use App\Models\Kos;
use App\Models\Booking;
use App\Models\Review;
use App\Models\PembagianDana;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class AdminService
{
    /**
     * Mengambil statistik agregat platform untuk dashboard admin.
     *
     * @return array
     */
    public function getDashboardStats(): array
    {
        $totalUsers = User::count();
        $totalPencari = User::where('role', 'pencari')->count();
        $totalPemilik = User::where('role', 'pemilik')->count();
        $totalKos = Kos::count();
        $totalKosAktif = Kos::where('status', 'aktif')->count();
        $totalKosPending = Kos::where('status', 'pending')->count();
        $totalBookingSukses = Booking::whereIn('status', ['dibayar', 'aktif', 'selesai'])->count();
        $totalRevenuePlatform = (float) PembagianDana::sum('biaya_platform');

        Log::info('Admin retrieved dashboard statistics', [
            'admin_id' => Auth::id()
        ]);

        return [
            'total_users'            => $totalUsers,
            'total_pencari'          => $totalPencari,
            'total_pemilik'          => $totalPemilik,
            'total_kos'              => $totalKos,
            'total_kos_aktif'        => $totalKosAktif,
            'total_kos_pending'      => $totalKosPending,
            'total_booking_sukses'   => $totalBookingSukses,
            'total_revenue_platform' => $totalRevenuePlatform,
        ];
    }

    /**
     * Mengambil daftar seluruh ulasan.
     *
     * @return Collection
     */
    public function getAllReviews(): Collection
    {
        return Review::with(['user', 'kos'])->latest()->get();
    }

    /**
     * Mengambil daftar seluruh pengguna.
     *
     * @return Collection
     */
    public function getAllUsers(): Collection
    {
        return User::where('role', '!=', 'admin')->latest()->get();
    }

    /**
     * Mengubah status pengguna (aktif/nonaktif).
     *
     * @param int $id
     * @param string $status
     * @return User
     */
    public function updateUserStatus(int $id, string $status): User
    {
        $user = User::findOrFail($id);
        $oldStatus = $user->status;
        $user->status = $status;
        $user->save();

        Log::info('Admin updated user status', [
            'admin_id'   => Auth::id(),
            'user_id'    => $user->id,
            'old_status' => $oldStatus,
            'new_status' => $status,
        ]);

        return $user;
    }

    /**
     * Mengambil daftar seluruh kos beserta data pemiliknya.
     *
     * @return Collection
     */
    public function getAllKos(): Collection
    {
        return Kos::with('pemilik')->latest()->get();
    }

    /**
     * Mengubah status kos (aktif/nonaktif/pending).
     *
     * @param int $id
     * @param string $status
     * @return Kos
     */
    public function updateKosStatus(int $id, string $status): Kos
    {
        $kos = Kos::findOrFail($id);
        $oldStatus = $kos->status;
        $targetStatus = strtolower($status);

        if ($targetStatus === 'rejected') {
            $targetStatus = 'ditolak';
        }

        $kos->status = $targetStatus;
        try {
            $kos->save();
        } catch (\Throwable $e) {
            Log::warning("Fallback status kos ke nonaktif karena enum DB: " . $e->getMessage());
            $kos->status = 'nonaktif';
            $kos->save();
        }

        Log::info('Admin updated kos status', [
            'admin_id'   => Auth::id(),
            'kos_id'     => $kos->id,
            'old_status' => $oldStatus,
            'new_status' => $kos->status,
        ]);

        return $kos;
    }

    /**
     * Menghapus ulasan spam/tidak pantas berdasarkan ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteReview(int $id): bool
    {
        $review = Review::findOrFail($id);
        $reviewId = $review->id;
        $review->delete();

        Log::info('Admin deleted review', [
            'admin_id'  => Auth::id(),
            'review_id' => $reviewId,
        ]);

        return true;
    }

    /**
     * Mengambil daftar pembagian dana beserta relasi pemilik dan booking.
     *
     * @return Collection
     */
    public function getDisbursements(): Collection
    {
        // Auto-sync transaksi Midtrans yang berhasil tetapi belum tercatat di pembagian_dana
        $activeBookings = Booking::whereIn('status', ['aktif', 'selesai', 'dibayar'])->with('kos')->get();
        foreach ($activeBookings as $b) {
            if ($b->kos && !PembagianDana::where('booking_id', $b->id)->exists()) {
                $totalTransaksi = (float) $b->total_harga;
                $biayaPlatform = $totalTransaksi * 0.03;
                $jatahPemilik = $totalTransaksi - $biayaPlatform;

                PembagianDana::create([
                    'booking_id'          => $b->id,
                    'pemilik_id'          => $b->kos->pemilik_id,
                    'total_transaksi'     => $totalTransaksi,
                    'persen_platform'     => 3.00,
                    'biaya_platform'      => $biayaPlatform,
                    'biaya_gateway'       => 0.00,
                    'jatah_pemilik'       => $jatahPemilik,
                    'status_disbursement' => 'pending',
                    'catatan'             => 'Pencatatan otomatis transaksi Midtrans',
                ]);
            }
        }

        return PembagianDana::with(['pemilik', 'booking.kos'])->latest()->get();
    }

    /**
     * Mengubah status disbursement (pending/diproses/selesai).
     *
     * @param int $id
     * @param string $status
     * @return PembagianDana
     */
    public function updateDisbursementStatus(int $id, string $status): PembagianDana
    {
        $disbursement = PembagianDana::findOrFail($id);
        $oldStatus = $disbursement->status_disbursement;
        $disbursement->status_disbursement = $status;
        $disbursement->save();

        Log::info('Admin updated disbursement status', [
            'admin_id'     => Auth::id(),
            'disbursement_id' => $disbursement->id,
            'old_status'   => $oldStatus,
            'new_status'   => $status,
        ]);

        return $disbursement;
    }
}