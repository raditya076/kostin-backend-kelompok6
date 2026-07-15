<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Kos;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class FavoriteService
{
    /**
     * Menyimpan kos ke dalam daftar favorit user.
     *
     * @param int $userId
     * @param int $kosId
     * @return Favorite
     * @throws \Exception
     */
    public function addFavorite(int $userId, int $kosId): Favorite
    {
        // Validasi agar kos tidak bisa difavoritkan dua kali oleh user yang sama
        $exists = Favorite::where('user_id', $userId)
            ->where('kos_id', $kosId)
            ->exists();

        if ($exists) {
            throw new \Exception('Kos ini sudah ada di dalam daftar favorit Anda.');
        }

        $favorite = Favorite::create([
            'user_id' => $userId,
            'kos_id'  => $kosId,
        ]);

        Log::info('Kos berhasil ditambahkan ke favorit', [
            'favorite_id' => $favorite->id,
            'user_id'     => $userId,
            'kos_id'      => $kosId,
        ]);

        return $favorite;
    }

    /**
     * Menghapus kos dari daftar favorit user.
     *
     * @param int $userId
     * @param int $kosId
     * @return void
     * @throws \Exception
     */
    public function removeFavorite(int $userId, int $kosId): void
    {
        $favorite = Favorite::where('user_id', $userId)
            ->where('kos_id', $kosId)
            ->first();

        if (!$favorite) {
            throw new \Exception('Kos tidak ditemukan di dalam daftar favorit Anda.');
        }

        $favorite->delete();

        Log::info('Kos berhasil dihapus dari favorit', [
            'user_id' => $userId,
            'kos_id'  => $kosId,
        ]);
    }

    /**
     * Mengambil daftar kos yang difavoritkan oleh user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getFavorites(int $userId): Collection
    {
        // Mengembalikan daftar model Kos beserta relasi foto (kosFoto) untuk memudahkan frontend
        return Kos::with('kosFoto')
            ->whereIn('id', Favorite::where('user_id', $userId)->pluck('kos_id'))
            ->get();
    }

    /**
     * Mengambil data detail untuk kos-kos yang dibandingkan (maksimal 3 kos).
     *
     * @param array $kosIds
     * @return Collection
     */
    public function compareKos(array $kosIds): Collection
    {
        // Membatasi maksimal 3 item array sesuai ketentuan
        $ids = array_slice($kosIds, 0, 3);

        // Hanya membandingkan kos yang berstatus aktif
        return Kos::with(['kosFoto', 'pemilik'])
            ->whereIn('id', $ids)
            ->where('status', 'aktif')
            ->get();
    }
}