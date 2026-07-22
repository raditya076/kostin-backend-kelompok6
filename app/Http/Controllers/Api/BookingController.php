<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookingController extends BaseController
{
    protected BookingService $bookingService;

    /**
     * Dependency Injection untuk BookingService.
     */
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Mengambil riwayat booking pencari atau booking masuk pemilik kos.
     *
     * GET /api/v1/bookings
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = $this->bookingService->getBookings(
            $request->user()->id,
            $request->user()->role
        );

        return $this->success($bookings, 'Daftar booking berhasil diambil');
    }

    /**
     * Menampilkan detail satu transaksi booking.
     *
     * GET /api/v1/bookings/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->findBookingDetails((int)$id, $request->user()->id);
            return $this->success($booking, 'Detail booking berhasil diambil');
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 400 && $code < 600) ? $code : 400;
            return $this->error($e->getMessage(), $statusCode);
        }
    }

    /**
     * Memverifikasi pembayaran transaksi booking.
     *
     * POST /api/v1/bookings/{id}/verify-payment
     */
    public function verifyPayment(Request $request, $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->verifyPayment((int)$id, $request->user()->id);
            return $this->success($booking, 'Status pembayaran berhasil diverifikasi dan diperbarui.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Membuat transaksi booking baru (Snap Token).
     *
     * POST /api/v1/bookings
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking(
                $request->user()->id,
                $request->validated()
            );

            return $this->success($booking, 'Pemesanan berhasil dibuat. Silakan lakukan pembayaran.', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Membatalkan booking oleh penyewa/pencari.
     *
     * POST /api/v1/bookings/{id}/cancel
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->cancelBooking(
                (int) $id,
                $request->user()->id
            );

            return $this->success($booking, 'Pemesanan berhasil dibatalkan');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Menyelesaikan booking oleh pemilik kos.
     *
     * POST /api/v1/bookings/{id}/complete
     */
    public function complete(Request $request, $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->completeBooking(
                (int) $id,
                $request->user()->id
            );

            return $this->success($booking, 'Sewa kos telah berhasil diselesaikan');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}