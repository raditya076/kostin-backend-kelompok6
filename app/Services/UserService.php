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
        $user->update([
            'nama'                  => $data['nama'] ?? $user->nama,
            'no_hp'                 => $data['no_hp'] ?? $user->no_hp,
            'nama_bank'             => $data['nama_bank'] ?? $user->nama_bank,
            'nomor_rekening'        => $data['nomor_rekening'] ?? $user->nomor_rekening,
            'nama_pemilik_rekening' => $data['nama_pemilik_rekening'] ?? $user->nama_pemilik_rekening,
        ]);

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