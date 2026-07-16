<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StoreReviewRequest;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;

class ReviewController extends BaseController
{
    protected ReviewService $reviewService;

    /**
     * Dependency Injection untuk ReviewService.
     */
    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Menambahkan ulasan baru ke Kos.
     *
     * POST /api/v1/kos/{id}/reviews
     */
    public function store(StoreReviewRequest $request, $id): JsonResponse
    {
        try {
            $review = $this->reviewService->createReview(
                $request->user()->id,
                (int) $id,
                $request->validated()
            );

            return $this->success($review, 'Ulasan berhasil ditambahkan', 201);
        } catch (\Exception $e) {
            $code = $e->getCode();
            // Menentukan status code HTTP yang valid (400-599), default ke 400 jika tidak diset
            $statusCode = ($code >= 400 && $code < 600) ? $code : 400;
            return $this->error($e->getMessage(), $statusCode);
        }
    }

    /**
     * Mengambil daftar ulasan untuk Kos tertentu.
     *
     * GET /api/v1/kos/{id}/reviews
     */
    public function index($id): JsonResponse
    {
        try {
            $reviews = $this->reviewService->getReviewsByKos((int) $id);
            return $this->success($reviews, 'Daftar ulasan berhasil diambil');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}