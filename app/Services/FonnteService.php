<?php

namespace App\Services;

use App\Helpers\WhatsAppHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $apiUrl;
    protected string $token;

    public function __construct()
    {
        $this->apiUrl = env('FONNTE_API_URL', 'https://api.fonnte.com/send');
        $this->token = env('FONNTE_TOKEN', '');
    }

    /**
     * Mengirim pesan WhatsApp melalui API Fonnte.
     *
     * @param string $target Nomor tujuan (akan dinormalisasi otomatis)
     * @param string $message Isi pesan WhatsApp
     * @return array Response dari Fonnte API
     */
    public function sendMessage(string $target, string $message): array
    {
        $formattedTarget = WhatsAppHelper::formatToWhatsApp($target);

        if (empty($formattedTarget)) {
            Log::error('FonnteService: Gagal mengirim pesan. Nomor target kosong atau tidak valid.', [
                'raw_target' => $target,
            ]);
            return [
                'status'  => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->apiUrl, [
                'target'      => $formattedTarget,
                'message'     => $message,
                'countryCode' => '62',
            ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === true) {
                Log::info('FonnteService: Notifikasi WhatsApp berhasil dikirim', [
                    'target'   => $formattedTarget,
                    'response' => $responseData,
                ]);

                return [
                    'status' => true,
                    'data'   => $responseData,
                ];
            }

            Log::error('FonnteService: API Fonnte mengembalikan status gagal', [
                'target'   => $formattedTarget,
                'response' => $responseData,
            ]);

            return [
                'status'   => false,
                'message'  => $responseData['reason'] ?? 'Gagal mengirim pesan via Fonnte.',
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('FonnteService: Exception terjadi saat mengirim notifikasi WhatsApp', [
                'target' => $formattedTarget,
                'error'  => $e->getMessage(),
            ]);

            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat mengirim WhatsApp: ' . $e->getMessage(),
            ];
        }
    }
}