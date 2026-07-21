<?php

namespace App\Http\Controllers\Api;

use App\Helpers\WhatsAppHelper;
use App\Http\Requests\StoreChatRequest;
use App\Models\Kos;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ChatController extends BaseController
{
    /**
     * Endpoint POST /api/v1/kos/{id}/tanya
     * Menggenerasi link redirect https://wa.me/ untuk obrolan langsung dengan pemilik kos.
     *
     * @param StoreChatRequest $request
     * @param int $id ID Kos
     * @return JsonResponse
     */
    public function tanyaPemilik(StoreChatRequest $request, int $id): JsonResponse
    {
        $kos = Kos::with('pemilik')->find($id);

        if (!$kos) {
            return $this->error('Data kos tidak ditemukan.', 404);
        }

        $pemilik = $kos->pemilik;
        if (!$pemilik || empty($pemilik->no_hp)) {
            return $this->error('Nomor WhatsApp pemilik kos tidak tersedia.', 400);
        }

        $penyewa = $request->user();
        $pesanInput = $request->validated('pesan');

        // Normalisasi nomor HP pemilik kos
        $formattedPhone = WhatsAppHelper::formatToWhatsApp($pemilik->no_hp);

        // Template pesan WhatsApp
        $textMessage = "Halo, saya {$penyewa->nama}. Saya ingin bertanya mengenai kos *{$kos->nama_kos}*:\n\n\"{$pesanInput}\"";
        
        // Buat wa.me link
        $waLink = "https://wa.me/{$formattedPhone}?text=" . urlencode($textMessage);

        Log::info('ChatController: Link WhatsApp tanya pemilik berhasil dibuat', [
            'kos_id'     => $kos->id,
            'penyewa_id' => $penyewa->id,
            'pemilik_id' => $pemilik->id,
            'wa_link'    => $waLink,
        ]);

        return $this->success([
            'wa_link' => $waLink,
        ], 'Link WA berhasil dibuat');
    }
}