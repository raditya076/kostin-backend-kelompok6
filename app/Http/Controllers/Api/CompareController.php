<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\CompareKosRequest;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;

class CompareController extends BaseController
{
    protected FavoriteService $favoriteService;

    /**
     * Dependency Injection untuk FavoriteService.
     */
    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * Membandingkan detail dari beberapa kos (maksimal 3).
     *
     * GET /api/v1/kos/compare
     */
    public function compare(CompareKosRequest $request): JsonResponse
    {
        try {
            $kosList = $this->favoriteService->compareKos($request->validated()['ids']);
            return $this->success($kosList, 'Perbandingan data kos berhasil diambil');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}