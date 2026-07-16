<?php

namespace App\Http\Controllers\Api;

use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends BaseController
{
    protected BookingService $bookingService;

    /**
     * PaymentController constructor.
     *
     * @param BookingService $bookingService
     */
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Menangani webhook notifikasi pembayaran dari Midtrans.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            // Mengirim payload JSON mentah ke Service Layer
            $result = $this->bookingService->processWebhook($request->all());
            
            // Kembalikan response sukses standar asdos (BaseController success)
            return $this->success($result, 'Webhook processed successfully');
        } catch (\Exception $e) {
            // Jika terjadi kegagalan verifikasi signature (Exception dengan code 403)
            if ($e->getCode() === 403) {
                return $this->error($e->getMessage(), 403);
            }
            
            // Error umum sistem lainnya
            return $this->error('Failed to process webhook: ' . $e->getMessage(), 500);
        }
    }
}