<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class UserService
{
    /**
     * Memperbarui detail informasi profil user.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $updateData = [];
        if (array_key_exists('nama', $data) && $data['nama'] !== null) {
            $updateData['nama'] = $data['nama'];
        }
        if (array_key_exists('no_hp', $data) && $data['no_hp'] !== null) {
            $updateData['no_hp'] = $data['no_hp'];
        }
        if (array_key_exists('nama_bank', $data)) {
            $updateData['nama_bank'] = $data['nama_bank'];
        }
        if (array_key_exists('nomor_rekening', $data)) {
            $updateData['nomor_rekening'] = $data['nomor_rekening'];
        }
        if (array_key_exists('nama_pemilik_rekening', $data)) {
            $updateData['nama_pemilik_rekening'] = $data['nama_pemilik_rekening'];
        }

        // Hanya update jenis_kelamin, tanggal_lahir, dan alamat jika kolomnya sudah ada di database
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'jenis_kelamin') && array_key_exists('jenis_kelamin', $data)) {
            $updateData['jenis_kelamin'] = $data['jenis_kelamin'];
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'tanggal_lahir') && array_key_exists('tanggal_lahir', $data)) {
            $updateData['tanggal_lahir'] = $data['tanggal_lahir'];
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'alamat') && array_key_exists('alamat', $data)) {
            $updateData['alamat'] = $data['alamat'];
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        Log::info('User profile details updated successfully', [
            'user_id'        => $user->id,
            'updated_fields' => array_keys($data)
        ]);

        return $user;
    }

    /**
     * Memproses upload dan memperbarui foto profil user.
     *
     * @param User $user
     * @param UploadedFile $photo
     * @return User
     */
    public function updateProfilePhoto(User $user, UploadedFile $photo): User
    {
        // 1. Hapus foto profil lama dari storage jika ada
        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
            Log::info('Old profile photo deleted from storage', [
                'user_id'   => $user->id,
                'old_photo' => $user->foto_profil
            ]);
        }

        // 2. Simpan foto profil baru ke folder 'profiles' di disk 'public'
        $path = $photo->store('profiles', 'public');

        // 3. Simpan path relatif foto profil ke database
        $user->update([
            'foto_profil' => $path
        ]);

        Log::info('User profile photo updated successfully', [
            'user_id'        => $user->id,
            'new_photo_path' => $path
        ]);

        return $user;
    }
}