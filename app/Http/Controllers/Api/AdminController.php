<?php

namespace App\Http\Controllers\Api;

use App\Services\AdminService;
use App\Http\Requests\UpdateUserStatusRequest;
use App\Http\Requests\UpdateKosStatusRequest;
use App\Http\Requests\UpdateDisbursementStatusRequest;
use Illuminate\Http\JsonResponse;

class AdminController extends BaseController
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * GET /api/v1/admin/dashboard
     * Mengambil statistik agregat platform.
     */
    public function dashboard(): JsonResponse
    {
        $stats = $this->adminService->getDashboardStats();
        return $this->success($stats, 'Statistik dashboard admin berhasil diambil');
    }

    /**
     * GET /api/v1/admin/users
     * Mengambil seluruh daftar pengguna.
     */
    public function users(): JsonResponse
    {
        $users = $this->adminService->getAllUsers();
        return $this->success($users, 'Daftar pengguna berhasil diambil');
    }

    /**
     * PUT /api/v1/admin/users/{id}/status
     * Mengubah status aktif/nonaktif pengguna.
     */
    public function updateUserStatus(UpdateUserStatusRequest $request, int $id): JsonResponse
    {
        $user = $this->adminService->updateUserStatus($id, $request->validated('status'));
        return $this->success($user, 'Status pengguna berhasil diperbarui');
    }

    /**
     * GET /api/v1/admin/kos
     * Mengambil seluruh daftar kos.
     */
    public function kos(): JsonResponse
    {
        $kosList = $this->adminService->getAllKos();
        return $this->success($kosList, 'Daftar kos berhasil diambil');
    }

    /**
     * PUT /api/v1/admin/kos/{id}/status
     * Mengubah status kos (aktif/nonaktif/pending).
     */
    public function updateKosStatus(UpdateKosStatusRequest $request, int $id): JsonResponse
    {
        $kos = $this->adminService->updateKosStatus($id, $request->validated('status'));
        return $this->success($kos, 'Status kos berhasil diperbarui');
    }

    /**
     * GET /api/v1/admin/reviews
     * Mengambil daftar seluruh ulasan.
     */
    public function reviews(): JsonResponse
    {
        $reviews = $this->adminService->getAllReviews();
        return $this->success($reviews, 'Daftar ulasan berhasil diambil');
    }

    /**
     * DELETE /api/v1/admin/reviews/{id}
     * Menghapus ulasan tidak pantas atau spam.
     */
    public function deleteReview(int $id): JsonResponse
    {
        $this->adminService->deleteReview($id);
        return $this->success(null, 'Ulasan berhasil dihapus oleh admin');
    }

    /**
     * GET /api/v1/admin/disbursements
     * Mengambil daftar pelacakan disbursement dana ke pemilik kos.
     */
    public function disbursements(): JsonResponse
    {
        $disbursements = $this->adminService->getDisbursements();
        return $this->success($disbursements, 'Daftar disbursement dana berhasil diambil');
    }

    /**
     * PUT /api/v1/admin/disbursements/{id}/status
     * Mengubah status disbursement (pending/diproses/selesai).
     */
    public function updateDisbursementStatus(UpdateDisbursementStatusRequest $request, int $id): JsonResponse
    {
        $disbursement = $this->adminService->updateDisbursementStatus($id, $request->validated('status'));
        return $this->success($disbursement, 'Status disbursement dana berhasil diperbarui');
    }
}