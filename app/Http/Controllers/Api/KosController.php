<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StoreKosRequest;
use App\Http\Requests\UpdateKosRequest;
use App\Services\KosService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KosController extends BaseController
{
    protected KosService $kosService;

    /**
     * Dependency Injection untuk KosService.
     */
    public function __construct(KosService $kosService)
    {
        $this->kosService = $kosService;
    }

    /**
     * Menampilkan daftar seluruh kos milik pemilik terautentikasi.
     *
     * GET /api/v1/owner/kos
     */
    public function index(Request $request): JsonResponse
    {
        $kos = $this->kosService->getOwnerKos($request->user()->id);
        return $this->success($kos, 'Daftar properti kos berhasil diambil');
    }

    /**
     * Menyimpan data kos baru beserta file upload.
     *
     * POST /api/v1/owner/kos
     */
    public function store(StoreKosRequest $request): JsonResponse
    {
        $fotoUtama = $request->file('foto_utama');
        $kosFoto = $request->file('kos_foto') ?? [];

        $kos = $this->kosService->createKos(
            $request->validated(),
            $request->user()->id,
            $fotoUtama,
            $kosFoto
        );

        return $this->success($kos, 'Properti kos berhasil dibuat', 201);
    }

    /**
     * Mengupdate data kos (memvalidasi kepemilikan).
     *
     * PUT /api/v1/owner/kos/{id}
     */
    public function update(UpdateKosRequest $request, $id): JsonResponse
    {
        try {
            $fotoUtama = $request->file('foto_utama');
            $kos = $this->kosService->updateKos(
                $id,
                $request->validated(),
                $request->user()->id,
                $fotoUtama
            );

            return $this->success($kos, 'Properti kos berhasil diperbarui');
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->error('Properti kos tidak ditemukan', 404);
        }
    }

    /**
     * Menghapus kos beserta seluruh berkas fotonya.
     *
     * DELETE /api/v1/owner/kos/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $this->kosService->deleteKos($id, $request->user()->id);
            return $this->success(null, 'Properti kos berhasil dihapus');
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->error('Properti kos tidak ditemukan', 404);
        }
    }
}