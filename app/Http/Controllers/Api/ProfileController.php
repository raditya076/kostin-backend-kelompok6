<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    protected UserService $userService;

    /**
     * Dependency Injection untuk UserService.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Mengambil dan menampilkan data detail user yang sedang login.
     *
     * GET /api/v1/profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        return $this->success($user, 'Profil user berhasil diambil');
    }

    /**
     * Memperbarui informasi teks detail user.
     *
     * PUT /api/v1/profile
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $updatedUser = $this->userService->updateProfile($user, $request->validated());

        return $this->success($updatedUser, 'Profil user berhasil diperbarui');
    }

    /**
     * Mengunggah dan memperbarui file foto profil.
     *
     * POST /api/v1/profile/photo
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'foto_profil' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $user = $request->user();
        $photo = $request->file('foto_profil');

        $updatedUser = $this->userService->updateProfilePhoto($user, $photo);

        return $this->success($updatedUser, 'Foto profil berhasil diperbarui');
    }
}