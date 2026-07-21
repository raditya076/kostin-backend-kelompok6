<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database secara aman.
     */
    public function run(): void
    {
        // Ambil email & password dari file .env (jika tidak ada, gunakan fallback untuk local testing)
        $adminEmail = env('ADMIN_DEFAULT_EMAIL', 'admin@gmail.com');
        $adminPassword = env('ADMIN_DEFAULT_PASSWORD', 'password123');

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'nama'     => 'Super Admin Kostin',
                'password' => Hash::make($adminPassword),
                'role'     => 'admin',
                'status'   => 'aktif',
            ]
        );
    }
}