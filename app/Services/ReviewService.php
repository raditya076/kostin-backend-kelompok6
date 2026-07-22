<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class ReviewService
{
    /**
     * Membuat ulasan dan rating baru untuk Kos.
     *
     * @param int $userId
     * @param int $kosId
     * @param array $data
     * @return Review
     * @throws \Exception
     */
    public function createReview(int $userId, int $kosId, array $data): Review
    {
        // 1. Validasi Sewa: Pastikan user memiliki booking dengan status 'selesai' atau 'aktif' untuk kos ini
        $hasCompletedBooking = Booking::where('penyewa_id', $userId)
            ->where('kos_id', $kosId)
            ->whereIn('status', ['selesai', 'aktif'])
            ->exists();

        if (!$hasCompletedBooking) {
            throw new \Exception("Hanya penyewa yang telah memesan dan menyelesaikan pembayaran di kos ini yang dapat memberi ulasan", 403);
        }

        // 2. Validasi Duplikat: Pastikan user belum pernah mereview kos ini sebelumnya
        $hasReviewed = Review::where('user_id', $userId)
            ->where('kos_id', $kosId)
            ->exists();

        if ($hasReviewed) {
            throw new \Exception("Anda sudah memberikan ulasan untuk kos ini", 400);
        }

        // 3. Simpan data ulasan baru ke database
        $review = Review::create([
            'user_id'    => $userId,
            'kos_id'     => $kosId,
            'rating'     => $data['rating'],
            'judul'      => $data['judul'] ?? null,
            'isi_ulasan' => $data['isi_ulasan'],
        ]);

        // 4. Catat Log::info ketika ulasan sukses didaftarkan
        Log::info('Ulasan sukses didaftarkan', [
            'review_id' => $review->id,
            'user_id'   => $userId,
            'kos_id'    => $kosId,
            'rating'    => $review->rating,
        ]);

        return $review;
    }

    /**
     * Mengambil daftar ulasan untuk kos terkait beserta data user (nama pengulas).
     *
     * @param int $kosId
     * @return Collection
     */
    public function getReviewsByKos(int $kosId): Collection
    {
        return Review::with('user:id,nama')
            ->where('kos_id', $kosId)
            ->get();
    }
}