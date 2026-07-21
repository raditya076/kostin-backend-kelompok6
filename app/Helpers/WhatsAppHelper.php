<?php

namespace App\Helpers;

class WhatsAppHelper
{
    /**
     * Normalisasi nomor telepon ke format WhatsApp internasional tanpa tanda + (contoh: 628123456789).
     *
     * @param string|null $phone
     * @return string
     */
    public static function formatToWhatsApp(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Hapus semua karakter non-digit (spasi, strip, +, dll)
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali dengan 0, ubah menjadi 62
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }
}

if (!function_exists('formatToWhatsApp')) {
    /**
     * Global helper wrapper function formatToWhatsApp.
     *
     * @param string|null $phone
     * @return string
     */
    function formatToWhatsApp(?string $phone): string
    {
        return \App\Helpers\WhatsAppHelper::formatToWhatsApp($phone);
    }
}