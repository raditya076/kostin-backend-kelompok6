<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\AddFavoriteRequest;
use App\Services\FavoriteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends BaseController
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
     * Menampilkan daftar kos favorit milik user terautentikasi.
     *
     * GET /api/v1/favorites
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $this->favoriteService->getFavorites($request->user()->id);
        return $this->success($favorites, 'Daftar kos favorit berhasil diambil');
    }

    /**
     * Menambahkan kos ke dalam daftar favorit.
     *
     * POST /api/v1/favorites
     */
    public function store(AddFavoriteRequest $request): JsonResponse
    {
        try {
            $favorite = $this->favoriteService->addFavorite(
                $request->user()->id,
                $request->validated()['kos_id']
            );
            return $this->success($favorite, 'Kos berhasil ditambahkan ke favorit', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Menghapus kos dari daftar favorit.
     *
     * DELETE /api/v1/favorites/{kos_id}
     */
    public function destroy(Request $request, $kos_id): JsonResponse
    {
        try {
            $this->favoriteService->removeFavorite(
                $request->user()->id,
                (int)$kos_id
            );
            return $this->success(null, 'Kos berhasil dihapus dari favorit');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}