<?php

namespace App\Services;

use App\Models\Kos;
use App\Models\KosFoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KosService
{
    /**
     * Menampilkan seluruh kos milik pemilik terautentikasi.
     *
     * @param int $ownerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOwnerKos(int $ownerId)
    {
        return Kos::with('fotos')->where('pemilik_id', $ownerId)->get();
    }

    /**
     * Menyimpan data kos baru ke database beserta foto properti.
     *
     * @param array $data
     * @param int $ownerId
     * @param UploadedFile|null $fotoUtama
     * @param array $kosFoto
     * @return Kos
     */
    public function createKos(array $data, int $ownerId, ?UploadedFile $fotoUtama = null, array $kosFoto = []): Kos
    {
        $data['pemilik_id'] = $ownerId;

        // Upload foto utama jika ada
        if ($fotoUtama) {
            $data['foto_utama'] = $fotoUtama->store('kos/utama', 'public');
        }

        // Default status adalah pending sesuai skema migration
        $data['status'] = $data['status'] ?? 'pending';

        $kos = Kos::create($data);

        // Upload galeri foto jika ada
        if (!empty($kosFoto)) {
            foreach ($kosFoto as $index => $file) {
                if ($file instanceof UploadedFile) {
                    $path = $file->store('kos/galeri', 'public');
                    KosFoto::create([
                        'kos_id'    => $kos->id,
                        'nama_file' => $path,
                        'urutan'    => $index,
                    ]);
                }
            }
        }

        Log::info('Kos berhasil dibuat', [
            'kos_id'     => $kos->id,
            'pemilik_id' => $ownerId,
            'nama_kos'   => $kos->nama_kos,
        ]);

        return $kos->load('fotos');
    }

    /**
     * Memperbarui data kos (memvalidasi kepemilikan kos).
     *
     * @param int $id
     * @param array $data
     * @param int $ownerId
     * @param UploadedFile|null $fotoUtama
     * @return Kos
     * @throws AuthorizationException
     */
    public function updateKos(int $id, array $data, int $ownerId, ?UploadedFile $fotoUtama = null): Kos
    {
        $kos = Kos::findOrFail($id);

        // Validasi kepemilikan
        if ($kos->pemilik_id !== $ownerId) {
            throw new AuthorizationException("Anda tidak memiliki hak akses untuk memperbarui kos ini.");
        }

        // Upload foto utama baru jika ada, dan hapus yang lama
        if ($fotoUtama) {
            if ($kos->foto_utama) {
                Storage::disk('public')->delete($kos->foto_utama);
            }
            $data['foto_utama'] = $fotoUtama->store('kos/utama', 'public');
        }

        $kos->update($data);

        Log::info('Kos berhasil diperbarui', [
            'kos_id'     => $kos->id,
            'pemilik_id' => $ownerId,
        ]);

        return $kos->load('fotos');
    }

    /**
     * Menghapus kos beserta seluruh berkas foto terkait dari local storage.
     *
     * @param int $id
     * @param int $ownerId
     * @return void
     * @throws AuthorizationException
     */
    public function deleteKos(int $id, int $ownerId): void
    {
        $kos = Kos::with('fotos')->findOrFail($id);

        // Validasi kepemilikan
        if ($kos->pemilik_id !== $ownerId) {
            throw new AuthorizationException("Anda tidak memiliki hak akses untuk menghapus kos ini.");
        }

        // Hapus file foto utama dari storage
        if ($kos->foto_utama) {
            Storage::disk('public')->delete($kos->foto_utama);
        }

        // Hapus file galeri foto dari storage
        foreach ($kos->fotos as $foto) {
            Storage::disk('public')->delete($foto->nama_file);
        }

        // Hapus kos dari database (relasi kos_foto akan ikut terhapus karena cascadeOnDelete)
        $kos->delete();

        Log::info('Kos berhasil dihapus', [
            'kos_id'     => $id,
            'pemilik_id' => $ownerId,
        ]);
    }

    /**
     * Mencari kos berstatus aktif dengan filter pencarian dinamis (Pencari Side).
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(array $filters)
    {
        $query = Kos::with('kosFoto')->where('status', 'aktif');

        // Filter Kota
        if (!empty($filters['kota'])) {
            $query->where('kota', 'like', '%' . $filters['kota'] . '%');
        }

        // Filter Rentang Harga
        if (isset($filters['harga_min'])) {
            $query->where('harga_per_bulan', '>=', $filters['harga_min']);
        }
        if (isset($filters['harga_max'])) {
            $query->where('harga_per_bulan', '<=', $filters['harga_max']);
        }

        // Filter Tipe Kos
        if (!empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        // Filter Fasilitas (wifi, ac, kamar_mandi_dalam, parkir, dapur, laundry, security, cctv)
        $facilities = [
            'wifi', 'ac', 'kamar_mandi_dalam', 'parkir', 
            'dapur', 'laundry', 'security', 'cctv'
        ];

        foreach ($facilities as $facility) {
            if (isset($filters[$facility]) && $filters[$facility] === true) {
                $query->where($facility, true);
            }
        }

        $results = $query->get();

        Log::info('Pencarian kos aktif dilakukan', [
            'filters'         => $filters,
            'total_ditemukan' => $results->count()
        ]);

        return $results;
    }

    /**
     * Mengambil satu kos aktif beserta relasi foto dan reviews (Pencari Side).
     *
     * @param int $id
     * @return Kos
     * @throws ModelNotFoundException
     */
    public function findActiveDetails(int $id): Kos
    {
        // Memuat kos dengan relasi kosFoto (galeri) dan reviews (serta data user reviewer untuk detail lengkap)
        $kos = Kos::with(['kosFoto', 'reviews.user'])
            ->where('status', 'aktif')
            ->find($id);

        if (!$kos) {
            Log::warning('Percobaan pengambilan detail kos gagal: tidak ditemukan atau tidak aktif', [
                'kos_id' => $id
            ]);
            throw new ModelNotFoundException("Properti kos tidak ditemukan atau tidak aktif.");
        }

        Log::info('Detail kos aktif berhasil diambil', [
            'kos_id'   => $kos->id,
            'nama_kos' => $kos->nama_kos
        ]);

        return $kos;
    }
}